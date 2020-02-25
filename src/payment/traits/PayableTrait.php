<?php
namespace ant\payment\traits;

use ant\payment\interfaces\Payable;

trait PayableTrait {
	public function pay($amount, $currency = null) {
		$bill = $this->getBill();
		
		if (!isset($bill)) {
			if (YII_DEBUG) throw new \Exception('No bill to be paid. ');
			$this->billTo();
		}
		if ($bill->pay($amount)) {
			$this->trigger(Payable::EVENT_AFTER_PAYMENT_SUCCESS);
		}
		return $this;
	}
}