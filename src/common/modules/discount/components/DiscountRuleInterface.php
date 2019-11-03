<?php
namespace common\modules\discount\components;

interface DiscountRuleInterface {
	public function getDiscountForCartItem($cartItem);
	
	public function getDiscountForCart($cart);
}