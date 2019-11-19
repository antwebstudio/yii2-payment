<?php
namespace ant\payment\interfaces;

interface Billable
{
	const EVENT_AFTER_BILL_SUCCESS = 'afterBillSuccess';
	
	public function getDiscountAmount();
    
    public function getAbsorbedServiceCharges();
    
	public function getServiceCharges();
	
	public function getTaxCharges();

    public function getCurrency();

    //public function getIsFree();

    //public function getIsPaid();
	
    public function getDueAmount();

    //public function getCalculatedNetTotal();
	
    public function getSubtotal();
	
    public function getNetTotal();

    public function getBillItems();
}