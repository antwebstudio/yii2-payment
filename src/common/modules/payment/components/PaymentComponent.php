<?php
namespace common\modules\payment\components;

use Yii;
use yii\helpers\Url;

class PaymentComponent extends \yii\base\Component {
	public $paymentGateway;
	public $paymentGatewaySandbox;
	public $testMode = false;
	
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
}