<?php
namespace ant\payment\traits;

use ant\helpers\Currency;
use ant\payment\models\Invoice;

trait BillableActiveRecordTrait {
	use BillableTrait;

    public function getInvoice()
    {
        return $this->hasOne(Invoice::className(), ['id' => 'invoice_id']);
    }
	
	public function billTo($userId = null) {
		$invoice = Invoice::createFromBillableModel($this, $userId);
		$this->link('invoice', $invoice);
		return $invoice;
	}
}