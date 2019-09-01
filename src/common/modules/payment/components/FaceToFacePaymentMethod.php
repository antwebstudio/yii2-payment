<?php
namespace common\modules\payment\components;

use Yii;
use common\modules\payment\components\PaymentMethod;
use common\modules\payment\models\Invoice;

class FaceToFacePaymentMethod extends PaymentMethod
{
    protected $amount;
    protected $transactionId;

    public function setTransactionId($transactionId) {
        $this->transactionId = $transactionId;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
    }
	
	public function initGateway() {
		//$this->_gateway = new BankWireGateway;
	}
	
	public function getPurchaseRequestAmountParamName() {
		//return 'amount';
    }
	
	public function getPaymentDataForGateway($payableModel) {
		$invoice = ($payableModel instanceof Invoice) ? $payableModel : $payableModel->invoice;

		return [
			'invoice' => Invoice::encodeId($invoice->id),
			'amount' => issete($this->amount) ? $this->amount : $payableModel->getDueAmount(),
		];
	}
	
	public function getPaymentRecordData() {
        return [
			'amount' => $this->amount,
			//'ref_no' => '1',
			'currency' => 'MYR',
			'status' => \common\modules\payment\components\PaymentMethod::STATUS_SUCCESS,
			'is_valid' => 1,
			'signature' => '',
			'remark' => '',
			'merchant_code' => '',
		];
    }

    public function getPaymentRecord() {
        if (!isset($this->transactionId)) throw new \Exception('Transaction ID is not set. ');
        
		$payment = new \common\modules\payment\models\Payment([
			'transaction_id' => $this->transactionId,
			'payment_gateway' => self::class,
			'data' => '-',
		]);
		Yii::configure($payment, $this->getPaymentRecordData());
		
		return $payment;
	}
}