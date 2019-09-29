<?php
namespace ant\payment\components;

class IPay88PaymentMethod extends \common\modules\payment\components\PaymentMethod
{
	public function setConfig($gatewayConfig) {
		if (isset($gatewayConfig['sandboxUrl'])) $gatewayConfig['sandboxUrl'] = \yii\helpers\Url::to($gatewayConfig['sandboxUrl'], true);
		if (isset($gatewayConfig['sandboxRequeryUrl'])) $gatewayConfig['sandboxRequeryUrl'] = \yii\helpers\Url::to($gatewayConfig['sandboxRequeryUrl'], true);
		parent::setConfig($gatewayConfig);
	}
	
	public function initGateway() {
        $this->_gateway = \Omnipay\Omnipay::create('IPay88');
	}
	
	public function getPurchaseRequestAmountParamName() {
		return 'amount';
	}
	
	public function getRequeryData($data) {
		return [
			'Amount' => $data['amount'],
			'RefNo' => $data['id'],
		];
	}
	
	public function getPaymentRecordData() {
		$data = $this->_response->getData();
		
		return [
			'amount' => $data['Amount'],
			'ref_no' => $data['RefNo'],
			'currency' => $data['Currency'],
			'status' => $data['Status'] == '1' ? self::STATUS_SUCCESS : self::STATUS_ERROR,
			'signature' => $data['Signature'],
			'remark' => $data['Remark'],
			'merchant_code' => $this->_gateway->getMerchantCode(),

			'data' => is_array($data) ? json_encode($data) : $data,
		];
	}
}