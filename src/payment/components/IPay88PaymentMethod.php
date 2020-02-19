<?php
namespace ant\payment\components;

class IPay88PaymentMethod extends \ant\payment\components\PaymentMethod
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
	
	public function getPaymentDataForGateway($payable) {
		$data = parent::getPaymentDataForGateway($payable);
		if (isset($data)) return $data;
		
		$returnUrl = \Yii::$app->payment->getReturnUrl($this->name);
		$cancelUrl = \Yii::$app->payment->getCancelUrl($this->name);
		$referenceId = \Yii::$app->payment->getPaymentDescription($payable);
		$paymentDescription = \Yii::$app->payment->getPaymentDescription($payable);
		$paymentId = null;

		$cancelUrlParams = $returnUrlParams = \Yii::$app->request->get();
		array_unshift($returnUrlParams, $returnUrl);
		array_unshift($cancelUrlParams, $cancelUrl);
		$backendUrlParams = $returnUrlParams;
		$backendUrlParams['backend']  = 1;
		
		return [
			'amount' => $payable->getDueAmount(),
			'currency' => $payable->getCurrency(),
			'expires_in' =>  time() + 10,
			'card' => [
				'billingName' => $payable->billedTo->contactName,
				'email' => $payable->billedTo->email,
				'number' => $payable->billedTo->contact_number,
			],
			'paymentId' => $paymentId,
			'description' => $paymentDescription,
			'transactionId' => $referenceId,
			// 'card' => $formData,

			'returnUrl' => \yii\helpers\Url::to($returnUrlParams, true),
			'cancelUrl' => \yii\helpers\Url::to($cancelUrlParams, true),
			'backendUrl' => \yii\helpers\Url::to($backendUrlParams, true),
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