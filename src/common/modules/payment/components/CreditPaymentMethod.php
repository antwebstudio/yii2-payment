<?php
namespace common\modules\payment\components;

use Yii;
use common\modules\payment\components\PaymentMethod;

class CreditPaymentMethod extends PaymentMethod
{
    public $allowWhenBalanceNotEnough = false;
    public $subscriptionIdentity;

	/*public function setConfig($gatewayConfig) {
		if (isset($gatewayConfig['sandboxUrl'])) $gatewayConfig['sandboxUrl'] = \yii\helpers\Url::to($gatewayConfig['sandboxUrl'], true);
		if (isset($gatewayConfig['sandboxRequeryUrl'])) $gatewayConfig['sandboxRequeryUrl'] = \yii\helpers\Url::to($gatewayConfig['sandboxRequeryUrl'], true);
		parent::setConfig($gatewayConfig);
	}*/
	
	public function initGateway() {
        //$this->_gateway = \Omnipay\Omnipay::create('IPay88');
	}
	
	public function getPurchaseRequestAmountParamName() {
		return 'amount';
    }
    
    public function purchase($options) {
        $this->_response = new CreditPaymentMethodResponse();
        $this->_response->amount = $options['amount'];
        $this->_response->reference = $options['reference'];
        
        if (Yii::$app->subscription->getCreditBalance(Yii::$app->user->id) >= $options['amount']) {
            Yii::$app->subscription->useCredit(Yii::$app->user->id, $options['amount']);
            $this->_response->message = 'Payment succesfully.';
            $this->_response->success = true;
        } else {
            $this->_response->message = 'Payment failed, credit not enough, please reload.';
            $this->_response->success = false;
        }
        return $this->_response;
    }
	
	/*public function getRequeryData($data) {
		return [
			'Amount' => $data['amount'],
			'RefNo' => $data['id'],
		];
	}*/
	
	public function getPaymentRecordData() {
        $data = $this->_response->getData();
        
        $data['data'] = $data;
		
		return $data;
    }
    
}

class CreditPaymentMethodResponse  {
    public $amount;
    public $reference;
    public $success;
    public $message;

    public function getData() {
        return [
            'transaction_id' => $this->getTransactionReference(),
            'amount' => $this->getAmount(), 
            'currency' => 'credit',
            'status' => $this->isSuccessful() ? PaymentMethod::STATUS_SUCCESS : PaymentMethod::STATUS_ERROR,
        ];
    }

    public function getAmount() {
        return $this->amount;
    }

    public function isRedirect() {
        return false;
    }

    public function redirect() {

    }

    public function isSuccessful() {
        return $this->success;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getTransactionReference() {
        return $this->reference.'-'.time();
    }
}
?>
