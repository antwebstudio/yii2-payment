<?php
namespace ant\payment\traits;

use ant\helpers\Currency;
use ant\payment\models\Invoice;

trait BillableActiveRecordTrait {
	use BillableTrait;
	
	protected $billAttribute = 'invoice';
	protected $invoiceIdAttribute = 'invoice_id';
	
	public function getBill() {
		return $this->{$this->billAttribute};
	}

    public function getInvoice()
    {
        return $this->hasOne(Invoice::className(), ['id' => $this->invoiceIdAttribute]);
    }
	
	public function billTo($userId = null) {
		$invoice = Invoice::createFromBillableModel($this, $userId);
		$this->link('invoice', $invoice);
		return $invoice;
	}
}