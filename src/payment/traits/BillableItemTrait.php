<?php
namespace ant\payment\traits;

use ant\helpers\Currency;

trait BillableItemTrait {
	public function getDiscountedUnitPrice() {
		return $this->getUnitPrice() - $this->getDiscountAmount();
	}
	
	public function getDiscountAmount() {
		return $this->getDiscount();
	}
	
	public function getIncludedInSubtotal() {
		return true;
	}
}