<?php
namespace ant\discount\rule;

class CustomerRule extends DiscountRule implements \ant\discount\components\DiscountRuleInterface {
	public $userGroups;
	public $users;
	
	public $products;
	public $categories;
	
	public $priority = 20;
	
	public $discount_percent;
	
	public function getDiscountForCartItem($cartItem) {
		return $this->isShouldApply ? $cartItem->unit_price * $this->discount_percent / 100 + $this->discount_amount : 0;
	}
	
	public function getDiscountForCart($cart) {
		return $this->isShouldApply ? $cart->total * $this->discount_percent / 100 + $this->discount_amount : 0;
	}
}