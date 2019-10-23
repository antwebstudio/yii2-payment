<?php
namespace ant\payment\traits;

use ant\helpers\Currency;

trait BillableTrait {
	public function getNetTotal() {
		return Currency::rounding($this->getDiscountedUnitPrice() * $this->getQuantity());
	}
	
	public function getIncludedInSubtotal() {
		return true;
	}
	
	public function getDiscount() {
		return 0;
	}
}