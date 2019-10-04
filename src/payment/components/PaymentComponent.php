<?php
namespace ant\payment\components;

use Yii;
use yii\helpers\Url;

class PaymentComponent extends \yii\base\Component {
	const SESSION_PAYMENT_SUCCESS_URL = 'paymentSuccessUrl';
	
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
	
	public function getPaymentMethod($name) {
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
	
	public function getPaymentErrorUrl($payableModel) {
		if (is_callable($this->errorUrl)) {
			return call_user_func_array($this->errorUrl, [$payableModel]);
		} else if (isset($this->errorUrl)) {
			return $this->errorUrl;
		} else {
			return $payableModel->privateRoute;
		}
	}
	
	public function setPaymentSuccessUrl($url) {
		return \Yii::$app->session->set(self::SESSION_PAYMENT_SUCCESS_URL, $url);
	}
	
	public function getPaymentSuccessUrl($payableModel) {
		if (is_callable($this->successUrl)) {
			return call_user_func_array($this->successUrl, [$payableModel]);
		} else {
			return \Yii::$app->session->get(self::SESSION_PAYMENT_SUCCESS_URL, $payableModel->privateRoute);
		}
	}
	
	public function getPaymentCancelUrl($payableModel) {
		if (is_callable($this->cancelUrl)) {
			return call_user_func_array($this->cancelUrl, [$payableModel]);
		} else {
			return $this->cancelUrl;
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
		
        return ($payable && (new $payable instanceof \common\modules\payment\models\Payable)) ? $payable : false;
    }

	protected function getExtraPayOptions()
	{
		return
		[
			'invoice' => '\common\modules\payment\models\Invoice',
			'order' => '\common\modules\order\models\Order'
		];
	}
}