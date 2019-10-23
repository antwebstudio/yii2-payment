<?php
namespace ant\payment\components;

use ant\payment\components\PayPalPaymentMethod;
use ant\payment\models\Payment;
use Omnipay\Omnipay;

class PayPalExpressGateway extends PayPalPaymentMethod {
	/**
	 * Error response:
	 * Array
		(
		[TOKEN] => EC-9PU06830A46581917
		[SUCCESSPAGEREDIRECTREQUESTED] => false
		[TIMESTAMP] => 2016-06-28T15:31:01Z
		[CORRELATIONID] => 2fd42468187ea
		[ACK] => Failure
		[VERSION] => 119.0
		[BUILD] => 000000
		[L_ERRORCODE0] => 10001
		[L_SHORTMESSAGE0] => Internal Error
		[L_LONGMESSAGE0] => Internal Error
		[L_SEVERITYCODE0] => Error
		)
	 */
	public function __construct() {
		$this->_gateway = Omnipay::create('PayPal_Express');
	}

	public function getAmount() {
		//if ($this->_response->isSuccessful()) {
			$data = $this->_response->getData();
			if (isset($data['PAYMENTINFO_0_AMT'])) return $data['PAYMENTINFO_0_AMT'];
		//}
		return null;
		//throw new Exception('Cannot get amount.'.print_r($data, 1));
	}

	public function getResponseData($name = null) {
		$data = $this->_response->getData();
		if (isset($name)) {
			return isset($data[$name]) ? $data[$name] : null;
		} else {
			return $data;
		}
	}

	public function isPaymentValid() {
		return $this->_response->isSuccessful();
	}
	
	public function getPaymentRecordData() {
		$data = $this->_response->getData();
		
		return [
			'amount' => $this->getAmount(),
			//'ref_no' => $data['RefNo'],
			'currency' => isset($data['PAYMENTINFO_0_CURRENCYCODE']) ? $data['PAYMENTINFO_0_CURRENCYCODE'] : '',
			'status' => isset($data['PAYMENTINFO_0_ACK']) && $data['PAYMENTINFO_0_ACK'] == 'Success' ? self::STATUS_SUCCESS : self::STATUS_ERROR,
			'error' => isset($data['L_LONGMESSAGE0']) ? $data['L_LONGMESSAGE0'] : '',
			//'signature' => $data['Signature'],
			//'remark' => $data['Remark'],
			//'merchant_code' => $this->_gateway->getMerchantCode(),

			'data' => is_array($data) ? json_encode($data) : $data,
		];
	}
}
