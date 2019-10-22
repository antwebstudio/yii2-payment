<?php
namespace ant\discount\rule;

class CartRule extends DiscountRule implements \ant\discount\components\DiscountRuleInterface {

	public $priority = 10;
	
	public function getIsShouldApply() {
		
	}
	
	public function getDiscountForCartItem($cartItem) {
		return 0;
	}
	
	public function getDiscountForCart($cart) {
		
	}
}