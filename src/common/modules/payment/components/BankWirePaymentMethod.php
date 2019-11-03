<?php
namespace common\modules\payment\components;

use Yii;
use common\modules\payment\components\PaymentMethod;
use common\modules\payment\models\Invoice;

class BankWirePaymentMethod extends PaymentMethod
{
	public $bankName;
	public $accountName;
	public $accountNumber;
	
    //public $allowWhenBalanceNotEnough = false;
    //public $subscriptionIdentity;

	/*public function setConfig($gatewayConfig) {
		if (isset($gatewayConfig['sandboxUrl'])) $gatewayConfig['sandboxUrl'] = \yii\helpers\Url::to($gatewayConfig['sandboxUrl'], true);
		if (isset($gatewayConfig['sandboxRequeryUrl'])) $gatewayConfig['sandboxRequeryUrl'] = \yii\helpers\Url::to($gatewayConfig['sandboxRequeryUrl'], true);
		parent::setConfig($gatewayConfig);
	}*/
	
	public function initGateway() {
		$this->_gateway = new BankWireGateway;
        //$this->_gateway = \Omnipay\Omnipay::create('IPay88');
	}
	
	public function getPurchaseRequestAmountParamName() {
		return 'amount';
    }
	
	public function getPaymentDataForGateway($payableModel) {
		$invoice = ($payableModel instanceof Invoice) ? $payableModel : $payableModel->invoice;

		return [
			'invoice' => Invoice::encodeId($invoice->id),
			'amount' => $payableModel->getDueAmount(),
		];
	}
    /*
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
    }*/
	
	/*public function getRequeryData($data) {
		return [
			'Amount' => $data['amount'],
			'RefNo' => $data['id'],
		];
	}*/
	
	public function getPaymentRecordData() {
        $data = []; // $this->_response->getData();
        
        $data['data'] = $data;
		
		return $data;
    }
    
}

class BankWireGateway extends \Omnipay\Common\AbstractGateway {
	public function getName() {
		return '';
	}

    public function purchase(array $parameters = array())
    {
		//throw new \Exception('purchase'.print_r($parameters,1));
        return $this->createRequest(BankWireGatewayRequest::class, $parameters);
    }

	public function setTestMode($value) {

	}

	public function send() {
		$request = new BankWireGatewayRequest(new \Guzzle\Http\Client, new \Symfony\Component\HttpFoundation\Request);
		return new BankWireGatewayResponse($request, []);
	}
}
class BankWireGatewayRequest extends \Omnipay\Common\Message\AbstractRequest{
	public function setInvoice($value) {
		$this->setParameter('invoice', $value);
	}

	public function sendData($data) {
		//throw new \Exception('set'.print_r($data,1));
        return $this->response = new BankWireGatewayResponse($this, $data);
	}

	public function getData() {
		return [
			'invoice' => $this->getParameter('invoice'),
			'amount' => $this->getParameter('amount'),
		];
	}
}

class BankWireGatewayResponse extends \Omnipay\Common\Message\AbstractResponse implements \Omnipay\Common\Message\RedirectResponseInterface  {
	public function isRedirect() {
		return true;
	}

	public function isSuccessful() {
		return true;
	}

	public function getRedirectUrl()
	{
		$invoiceId = isset($this->data['invoice']) ? $this->data['invoice'] : null;
		//throw new \Exception(print_r($this->getParameter('invoice'),1));
		return \yii\helpers\Url::to(['/payment/bank-wire/create', 'invoice' => $invoiceId]);
	}
	
	public function getRedirectMethod() {
		return 'GET';
	}

	public function getRedirectData() {
		throw new \Exception(print_r($this->data,1));
		return ['invoice' => $this->data['invoice']];
	}
}