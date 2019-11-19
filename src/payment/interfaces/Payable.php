<?php
namespace ant\payment\interfaces;

interface Payable
{
	const EVENT_AFTER_PAYMENT_SUCCESS = 'afterPaymentSuccess';
	
	public function pay($amount);
	
	//public function getServiceCharges();

    public function getCurrency();

    public function getIsFree();

    public function getIsPaid();
	
    public function getDueAmount();
	
	public function getReference();

    //public function getCalculatedTotalAmount();

    //public function getPaymentInvoiceItems();
	
	//public function getPaymentItems();
}
