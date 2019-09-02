<?php
namespace common\modules\discount\rule;

class CartRule extends DiscountRule implements \common\modules\discount\components\DiscountRuleInterface {

	public $priority = 10;
	
	public function getIsShouldApply() {
		
	}
	
	public function getDiscountForCartItem($cartItem) {
		return 0;
	}
	
	public function getDiscountForCart($cart) {
		
	}
}