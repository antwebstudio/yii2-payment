<?php
namespace ant\payment\components;

class PaymentRecordAdapter extends \yii\base\BaseObject {
	public $payment_gateway;
	public $is_valid;
	public $transaction_id;
	public $amount;
	public $currency;
	public $status;
	public $error;
	public $data;
	
	public $signature;
	public $ref_no;
	public $merchant_code;
	public $remark;
	
	public function getPaymentRecord() {
		$attributes = [
			'payment_gateway', 'is_valid', 'transaction_id', 'amount', 'currency', 'status', 'error', 'data',
			'signature', 'ref_no', 'merchant_code', 'remark',
		];
		$payment = new \ant\payment\models\Payment;
		
		foreach ($attributes as $attribute) {
			$payment->{$attribute} = $this->{$attribute};
		}
		
		return $payment;
	}
}