<?php
namespace ant\payment\traits;

use ant\helpers\Currency;

trait BillableTrait {
	/*public function billTo($userId = null) {
		//return Invoice::createFromBillableModel($this, $userId);
	}*/
	
	/*public function getNetTotal() {
		return Currency::rounding($this->getDiscountedUnitPrice() * $this->getQuantity());
	}*/
	
    public function getSubtotal() {
		$total = 0;
		foreach ($this->getBillItems() as $item) {
			$total += $item->getDiscountedUnitPrice() * $item->getQuantity();
		}
		return $total;
	}
	
	/*public function getIncludedInSubtotal() {
		return true;
	}*/
	
	public function getDiscount() {
		return 0;
	}
	
	public function getDiscountAmount() {
		return 0;
	}
    
    public function getAbsorbedServiceCharges() {
		return 0;
	}
    
	public function getServiceCharges() {
		return 0;
	}
	
	public function getTaxCharges() {
		return 0;
	}
	
    public function getDueAmount() {
		return $this->getNetTotal() - $this->getPaidAmount();
	}
	
    public function getNetTotal() {
		return $this->getSubtotal() + $this->getServiceCharges() - $this->getAbsorbedServiceCharges() + $this->getTaxCharges() - $this->getDiscountAmount() ;
	}

    public function getIsPaid() {
		return $this->getPaidAmount() == $this->getNetTotal();
	}
	
	public function getIsFree() {
		return $this->getNetTotal() == 0;
	}
}