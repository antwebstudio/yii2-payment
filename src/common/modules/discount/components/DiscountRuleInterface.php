<?php
namespace ant\discount\components;

interface DiscountRuleInterface {
	public function getDiscountForCartItem($cartItem);
	
	public function getDiscountForCart($cart);
}