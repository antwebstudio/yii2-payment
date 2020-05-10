<?php
namespace ant\payment\traits;

use ant\helpers\Currency;

trait BillableItemTrait {
	public function getIncludedInSubtotal() {
		return true;
	}
}