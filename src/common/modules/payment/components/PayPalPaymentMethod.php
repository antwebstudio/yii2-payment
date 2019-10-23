<?php
namespace ant\payment\components;

use ant\payment\components\PaymentMethod;

class PayPalPaymentMethod extends PaymentMethod
{
	public $gatewayType;
	
	public function initGateway($config = []) {
		$gatewayType = isset($config['gatewayType']) ? $config['gatewayType'] : 'PayPal_Express';
        $this->_gateway = \Omnipay\Omnipay::create($gatewayType);
	}
	
	public function createCard($options) {
		return $this->_gateway->createCard($options);
	}
	
	/*
	Example：
	
	[TOKEN] => EC-7S660970KM387623N
	[SUCCESSPAGEREDIRECTREQUESTED] => false
	[TIMESTAMP] => 2018-05-31T08:02:23Z
	[CORRELATIONID] => 5b69e32ca9e27
	[ACK] => SuccessWithWarning
	[VERSION] => 119.0
	[BUILD] => 46549960
	[L_ERRORCODE0] => 11607
	[L_SHORTMESSAGE0] => Duplicate Request
	[L_LONGMESSAGE0] => A successful transaction has already been completed for this token.
	[L_SEVERITYCODE0] => Warning
	[INSURANCEOPTIONSELECTED] => false
	[SHIPPINGOPTIONISDEFAULT] => false
	[PAYMENTINFO_0_TRANSACTIONID] => 2GA718416N2012254
	[PAYMENTINFO_0_TRANSACTIONTYPE] => expresscheckout
	[PAYMENTINFO_0_PAYMENTTYPE] => instant
	[PAYMENTINFO_0_ORDERTIME] => 2018-05-31T08:01:54Z
	[PAYMENTINFO_0_AMT] => 10.00
	[PAYMENTINFO_0_FEEAMT] => 2.34
	[PAYMENTINFO_0_TAXAMT] => 0.00
	[PAYMENTINFO_0_CURRENCYCODE] => MYR
	[PAYMENTINFO_0_PAYMENTSTATUS] => Completed
	[PAYMENTINFO_0_PENDINGREASON] => None
	[PAYMENTINFO_0_REASONCODE] => None
	[PAYMENTINFO_0_PROTECTIONELIGIBILITY] => Eligible
	[PAYMENTINFO_0_PROTECTIONELIGIBILITYTYPE] => ItemNotReceivedEligible,UnauthorizedPaymentEligible
	[PAYMENTINFO_0_SELLERPAYPALACCOUNTID] => chy1988-facilitator@gmail.com
	[PAYMENTINFO_0_SECUREMERCHANTACCOUNTID] => 6R6RT24KYUEBL
	[PAYMENTINFO_0_ERRORCODE] => 0
	[PAYMENTINFO_0_ACK] => Success
	*/
	
	public function getPaymentRecordData() {
		
		$data = $this->_response->getData();
		
		if (isset($data['L_ERRORCODE0']) && in_array($data['L_ERRORCODE0'], ['10002', '10406'])) {
			// Invalid credential
			throw new \Exception('('.$data['L_ERRORCODE0'].') '.$data['L_LONGMESSAGE0'].print_r($data,1));
		} else {
			return [
			//	'transaction_id' => 
				'amount' => $data['PAYMENTINFO_0_AMT'],
				//'ref_no' => $data['RefNo'],
				'currency' => $data['PAYMENTINFO_0_CURRENCYCODE'],
				'status' => $data['PAYMENTINFO_0_ERRORCODE'] == '0' ? self::STATUS_SUCCESS : self::STATUS_ERROR,
				//'signature' => $data['Signature'],
				//'remark' => $data['Remark'],
				'merchant_code' => $data['PAYMENTINFO_0_SECUREMERCHANTACCOUNTID'],
				'error' => isset($data['L_LONGMESSAGE0']) ? $data['L_LONGMESSAGE0'] : '',
				'data' => is_array($data) ? json_encode($data) : $data,
			];
		}
	}
	
    public function setClientId($clientId)
    {
		$this->_gateway->setClientId($clientId);
	}
	
    public function setSecret($secret)
    {
		$this->_gateway->setSecret($secret);
	}
	
    public function setUsername($username)
    {
		$this->_gateway->setUsername($username);
	}

	public function setPassword($password)
    {
		$this->_gateway->setPassword($password);
	}

	public function setSignature($signature)
    {
		$this->_gateway->setSignature($signature);
	}
	
	public function getPurchaseRequestAmountParamName() {
		return 'amount';
	}
}
?>
