<?php
namespace ant\payment\components;

use Yii;
use yii\helpers\Url;
use ant\payment\models\Invoice;
use ant\payment\interfaces\Payable;
//use ant\payment\models\Billable;
use ant\payment\events\PaymentEvent;

class PaymentComponent extends \yii\base\Component {
	const EVENT_PAYMENT_SUCCESS = 'paymentSuccess';
	const EVENT_PAYMENT_ERROR = 'paymentError';
	
	const SESSION_PAYMENT_SUCCESS_URL = 'paymentSuccessUrl';
	const SESSION_PAYMENT_URL = 'paymentUrl';
	
	public $paymentGateway;
	public $paymentGatewaySandbox;
	public $testMode = false;
	public $successUrl;
	public $errorUrl;
	
	protected $_paymentMethod = [];
	protected $_cancelUrl;
	
	protected function getPaymentMethodConfigs() {
		return $this->testMode ? $this->paymentGatewaySandbox : $this->paymentGateway;
	}
	
	protected function getPaymentMethodNames() {
		return array_keys($this->paymentMethodConfigs);
	}

    protected function handleResponse($paymentMethod, $response, $payable, $backend = false)
    {
		//$transaction = Yii::$app->db->beginTransaction();
		try {			
			if ($response->isRedirect()) {
				// redirect to offsite payment gateway
				$response->redirect();
			} elseif ($response->isSuccessful()) {
				$this->onPaymentSuccessful($paymentMethod, $payable, $response, $backend);
				return true;
			} else {
				// payment failed: display message to customer
				$this->onPaymentError($paymentMethod, $response, $payable);
				return false;
			}
			//$transaction->commit();
		} catch (\Exception $ex) {
			//$transaction->rollback();
			throw $ex;
		}
	}
	
	protected function ensurePaymentRecord($paymentMethod, $payable, $backend = false) {
		$payment = \ant\payment\models\Payment::findOne(['transaction_id' => $paymentMethod->paymentRecord->transaction_id]);
		
		if (!isset($payment)) {
			$mutex = Yii::$app->mutex;
			$mutexName = 'payment-'.$paymentMethod->paymentRecord->transaction_id;
			
			if ($mutex->acquire($mutexName)) {
				$payment = $paymentMethod->paymentRecord;
				if ($backend) $payment->backend_update = 1;
				if (!$payment->save()) throw new \Exception('Payment record failed to be saved. '.print_r($payment->errors, 1));
				
				//$mutex->release($mutexName);
			} else {
				sleep(1);
				$payment = $this->ensurePaymentRecord($paymentMethod, $payable, $backend);
			}
		}
		return $payment;
	}
	
	protected function assignPaymentToInvoice($payment, $invoice) {
		$payment->invoice_id = $invoice->id;
		if (!$payment->save()) throw new \Exception('Payment record failed to be saved. '.print_r($payment->errors, 1));
	}

    protected function onPaymentSuccessful($paymentMethod, $payable, $response, $backend = false)
    {
		$payment = $this->ensurePaymentRecord($paymentMethod, $payable, $backend);
		$invoice = $this->getInvoice($payable);
		$this->assignPaymentToInvoice($payment, $invoice);
		
        //$payable->pay($payment->amount);
		$invoice->pay($payment->amount);
		
		$payable->trigger(Payable::EVENT_AFTER_PAYMENT_SUCCESS);
		
		$this->trigger(self::EVENT_PAYMENT_SUCCESS, new PaymentEvent([
			'payable' => $payable,
			'response' => $response,
			'invoice' => $invoice,
		]));
	}

    protected function onPaymentError($paymentMethod, $response, $payable)
    {
		$payment = $this->ensurePaymentRecord($paymentMethod, $payable, $backend);
		$invoice = $this->getInvoice($payable);
		$this->assignPaymentToInvoice($payment, $invoice);
		
		$this->trigger(self::EVENT_PAYMENT_ERROR, new PaymentEvent([
			'payable' => $payable,
			'response' => $response,
			'invoice' => $invoice,
		]));
		//throw new Yii\web\HttpException(500, $response->getMessage());
    }
	
	public function completePaymentFromBackend($paymentMethod, $payable) {
		$response = $paymentMethod->completePurchase($paymentMethod->getPaymentDataForGateway($payable));

		$return = $this->handleResponse($paymentMethod, $response, $payable, true);
		
		if ($response->isSuccessful()) die('RECEIVEOK');
		
		return $response;
	}
	
	public function completePayment($paymentMethod, $payable) {
		$response = $paymentMethod->completePurchase($paymentMethod->getPaymentDataForGateway($payable));

		$return = $this->handleResponse($paymentMethod, $response, $payable);
		
		return $response;
	}
	
	public function pay($paymentMethod, $payable) {
		$transaction = Yii::$app->db->beginTransaction();
		// Payment gateway
		$response = $paymentMethod->purchase($paymentMethod->getPaymentDataForGateway($payable));

		$return = $this->handleResponse($paymentMethod, $response, $payable);

		$transaction->commit();
		
		return $response;
	}
	
	public function getAllPaymentMethodFor($model, $includedDisabled = false) {
		$configs = $this->paymentMethodConfigs;
		
		$enabledPaymentMethods = [];
		
		foreach ($configs as $name => $config) {
			if ($includedDisabled || !isset($config['enabled']) || $config['enabled']) {
				$paymentMethod = $this->getPaymentMethod($name);
				
				if ($includedDisabled || $paymentMethod->isEnabledFor($model)) {
					$enabledPaymentMethods[$name] = $paymentMethod;
				}
			}
		}
		return $enabledPaymentMethods;
	}
	
	public function getInvoice($payable) {
		// TODO: try to generalize this using payable interface
        if($payable instanceof Invoice) {
            return $payable;
		} else if(isset($payable->invoice)) {
			return $payable->invoice;
        } else if($payable instanceof \ant\payment\models\Billable) {
			if (YII_DEBUG) throw new \Exception('Use ant\payment\interfaces\Billable instead. ');
            return Invoice::createFromBillableModel($payable, Yii::$app->user->identity);
        } else if($payable instanceof \ant\payment\interfaces\Billable) {
			$mutex = Yii::$app->mutex;
			$mutexName = 'orderInvoice-'.$payable->id;
			
			if ($mutex->acquire($mutexName)) {
				$transaction = Yii::$app->db->beginTransaction();
				try {
					$invoice = $payable->billTo(Yii::$app->user->identity);
					$transaction->commit();
				} catch (\Exception $ex) {
					$mutex->release($mutexName);
					$transaction->rollback();
					throw $ex;
				}
			} else {
				sleep(1);
				$payable->refresh();
				$invoice = $this->getInvoice($payable);
			}
            return $invoice;
        } else {
			throw new \Exception('Not able to create invoice. ');
		}
	}
	
	public function getPaymentMethod($name) {
		$name = strlen($name) ? $name : 'ipay88';
		
		if (!isset($this->_paymentMethod[$name])) {
			$config = $this->paymentMethodConfigs[$name];
			$config['name'] = $name;
			
			$this->_paymentMethod[$name] = Yii::createObject($config);
			$this->_paymentMethod[$name]->setTestMode($this->testMode);
		}
		return $this->_paymentMethod[$name];
	}
	
	public function setCancelUrl($url) {
		$this->_cancelUrl = $url;
	}
	
	public function getCancelUrl($name = null) {
		return $this->_cancelUrl;
	}
	
	public function getReturnUrl($name = null) {
		return '/payment/default/complete-payment';
	}
	
	public function getPaymentErrorUrl($payable) {
		if (is_callable($this->errorUrl)) {
			return call_user_func_array($this->errorUrl, [$payable]);
		} else if (isset($this->errorUrl)) {
			return $this->errorUrl;
		} else {
			return $payable->privateRoute;
		}
	}
	
	public function setPaymentSuccessUrl($url) {
		if (is_callable($url)) {
			$this->successUrl = $url;
			// Session not accept callable closure.
		} else {
			return \Yii::$app->session->set(self::SESSION_PAYMENT_SUCCESS_URL, $url);
		}
	}
	
	public function getPaymentSuccessUrl($payable) {
		if (is_callable($this->successUrl)) {
			return call_user_func_array($this->successUrl, [$payable]);
		} else {
			return \Yii::$app->session->get(self::SESSION_PAYMENT_SUCCESS_URL, isset($this->successUrl) ? $this->successUrl : $payable->privateRoute);
		}
	}
	
	public function setPaymentCancelUrl($url) {
		if (is_callable($url)) {
			$this->cancelUrl = $url;
			// Session not accept callable closure.
		} else {
			return \Yii::$app->session->set(self::SESSION_PAYMENT_URL, $url);
		}
	}
	
	public function getPaymentCancelUrl($payable) {
		if (is_callable($this->cancelUrl)) {
			return call_user_func_array($this->cancelUrl, [$payable]);
		} else {
			return \Yii::$app->session->get(self::SESSION_PAYMENT_URL, $this->cancelUrl);
		}
	}
	
	public function getPayableModel($payType, $payId)
    {
        if (!$payableClass = $this->getPayableClass($payType)) {
			return false;
		}

        return $payableClass::findOne($payId);
    }

    protected function getPayableClass($payType = false)
    {
        //default pay option
        if(!$payType) return Invoice::className();

		//extra pay option
		if (isset($this->extraPayOptions[$payType])) {
			$payable = $this->extraPayOptions[$payType];
		} else {
			throw new \Exception('Payable model type "'.$payType.'" not defined. ');
		}
		
        return ($payable && (new $payable instanceof \ant\payment\interfaces\Payable)) ? $payable : false;
    }

	protected function getExtraPayOptions()
	{
		return
		[
			'invoice' => '\ant\payment\models\Invoice',
			'order' => '\ant\order\models\Order'
		];
	}
}