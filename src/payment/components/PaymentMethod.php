<?php
namespace ant\payment\components;

use Yii;
use Omnipay\Omnipay;
use yii\helpers\Url;
use yii\base\Component;
use ant\payment\models\Invoice;

abstract class PaymentMethod extends Component implements PaymentMethodInterface {
	const STATUS_SUCCESS = 0;
	const STATUS_ERROR = 1;
	const STATUS_PENDING = 2;
	
	public $name;
	public $enabled = true;
	public $overrideMethods = [];
	
	protected $_gateway;
	protected $_response;
	protected $_buttonOptions = [];
	
    public function __construct($config = [])
    {
		$this->initGateway($config);
		return parent::__construct($config);
    }
	
	public function hasOverrideMethod($methodName) {
		return isset($this->overrideMethods[$methodName]) && is_callable($this->overrideMethods[$methodName]);
	}
	
	public function callMethod($methodName, $params = []) {
		return call_user_func_array($this->overrideMethods[$methodName], $params);
	}

	public function setTestMode($isTestMode) {
		if (isset($this->_gateway)) {
			$this->_gateway->setTestMode($isTestMode);
		}
	}
	
	public function setConfig($gatewayConfig) {
		if (isset($this->_gateway)) {
			$this->_gateway->initialize($gatewayConfig);
		}
	}
	
	public function getResponse() {
		return $this->_response;
	}
	
	public function requery($options) {
		$this->_response = $this->_gateway->requery($this->getRequeryData($options))->send();
		return $this->_response;
	}

	public function purchase($options) {
		if (!isset($options)) throw new \Exception('Payment data is empty, please check "getPaymentDataForGateway" method for payment method. ');
		if (!isset($options[$this->getPurchaseRequestAmountParamName()])) throw new \Exception('Purchase request amount param name may set wrong, possible value will be: '.implode(', ', array_keys($options)));
		
		if (!$options[$this->getPurchaseRequestAmountParamName()]) throw new \Exception('Amount for purchase must be greater than 0. ');
		
		$this->_response = $this->_gateway->purchase($options)->send();
		return $this->_response;
	}

	public function completePurchase($options) {
		$this->_response = $this->_gateway->completePurchase($options)->send();
		return $this->_response;
	}
	
	public function pay($payable, $amount = null, $currency = null) {
		$paymentRecord = $this->getPaymentRecord();
		$paymentRecord->is_valid = 1;
		
		if ($payable instanceof Invoice) $paymentRecord->invoice_id = $payable->id;
		if (isset($amount)) $paymentRecord->amount = $amount;
		if (isset($currency)) $paymentRecord->currency = $currency;
		
		if (!$paymentRecord->save()) throw new \Exception(print_r($paymentRecord->errors, 1));
		return $paymentRecord;
	}

    /*public function isPaymentValid()
    {
        return $this->_response->isSuccessful();
    }*/
	
	public function getPaymentDataForGateway($payableModel) {
		if ($this->hasOverrideMethod('getPaymentDataForGateway')) {
			return $this->callMethod('getPaymentDataForGateway', [$payableModel, $this]);
		}
	}
	
	public function getPaymentRecord() {
		if (isset($this->_response)) {
			return $this->getPaymentRecordFromResponse();
		} else {
			$adapter = new PaymentRecordAdapter;
			Yii::configure($adapter, $this->getPaymentRecordData());
			
			$adapter->payment_gateway = $this::className();
			$adapter->data = $this->getRawData();
			$adapter->status = $adapter->is_valid ? 0 : 1;
		}
		return $adapter->getPaymentRecord();
	}
	
	protected function getRawData() {
		return isset($this->_response) ? $this->_response->getData() : ['empty'];
	}
	
	protected function getPaymentRecordFromResponse() {
		$adapter = new PaymentRecordAdapter;
		Yii::configure($adapter, $this->getPaymentRecordData());
		
		$adapter->payment_gateway = $this::className();
		$adapter->error = $this->_response->getMessage();
		$adapter->is_valid = $this->_response->isSuccessful() ? 1 : 0; // Should not use status code, 1 - valid (while status 0 - valid)
		$adapter->transaction_id = $this->_response->getTransactionReference();
		
		return $adapter->getPaymentRecord();
	}
	
	public function savePaymentRecord($invoice) {
		$payment = \ant\payment\models\Payment::findOne(['transaction_id' => $this->paymentRecord->transaction_id]);
		
		if (!isset($payment)) {
			$payment = $this->paymentRecord;
			$payment->invoice_id = $invoice->id;
			
			if (!$payment->save()) throw new \Exception('Payment record failed to be saved. '.(YII_DEBUG ? \yii\helpers\Html::errorSummary($payment) : ''));
		}
		return $payment;
	}
	
	public function getPayUrl($payable) {
		if (get_class($payable) == 'ant\order\models\Order') {
			$type = 'order';
		}
		return Url::to(['/payment/default/pay', 
			'payId' => $payable->id, 
			'cancelUrl' => \Yii::$app->payment->getCancelUrl(), 
			'type' => $type,
			'payMethod' => $this->name,
		]);
	}
	
	public function getPaymentUrl($type, $payId) {
		if (YII_DEBUG) throw new \Exception('DEPRECATED, use getPayUrl'); // 2020-2-14
		
		return Url::to(['/payment/default/pay', 
			'payId' => $payId, 
			'cancelUrl' => \Yii::$app->payment->getCancelUrl(), 
			'type' => $type,
			'payMethod' => $this->name,
		]);
	}
	
	public function setButtonOptions($options) {
		$this->_buttonOptions = $options;
	}
	
	public function getButtonOptions() {
		return \yii\helpers\ArrayHelper::merge([
			'label' => 'Process Payment',
			'class' => 'btn btn-primary',
		], $this->_buttonOptions);
	}
	
	public function setIconUrl($value) {
		$this->_iconUrl = $value;
	}
	
	public function isEnabledFor($payable) {
		if (is_callable($this->enabled)) {
			return call_user_func_array($this->enabled, [$payable]);
		}
		return $this->enabled;
	}

	/*
	public function setReturnUrl($value) {
		$this->_returnUrl = $value;
	}
	
	public function getReturnUrl() {
		if (isset($this->_returnUrl)) {
			return $this->_returnUrl;
		}
		return Yii::app()->getController()->createAbsoluteUrl('/payment/default/confirm');
	}*/

	/*public function renderFormBegin($output = true) {
		Yii::app()->controller->beginWidget('widgets.ActiveForm', array(
			'action' => $this->url,
		), $output);
	}

	public function renderPaymentButton($label = 'Make Payment', $htmlOptions = null, $output = true) {
		if ($this->amount > 0) {
			$this->renderFormBegin($output);
			$this->renderOnlyPaymentButton($label, $htmlOptions, $output);
			$this->renderFormEnd();
		} else {
			if ($output) {
				echo 'Paid';
			} else {
				return 'Paid';
			}
		}
	}

	public function renderOnlyPaymentButton($label = 'Make Payment', $htmlOptions = null, $output = true) {
		$html = '';
		foreach ($this->getFields() as $name => $value) {
			$html .= CHtml::hiddenField($name, $value);
		}
		$html .= CHtml::submitButton($label, $htmlOptions);

		if ($output) {
			echo $html;
		} else {
			return $html;
		}
	}

	public function renderFormEnd() {
		Yii::app()->controller->endWidget();
	}*/
}
