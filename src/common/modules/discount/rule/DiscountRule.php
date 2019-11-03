<?php
namespace common\modules\discount\rule;

use yii\helpers\ArrayHelper;

class DiscountRule extends \yii\base\Component {
	
	public $userGroups;
	public $users;
	
	public $products;
	public $categories;
	
	public $priority = 20;
	
	public $discount_percent;
	public $discount_amount;
	
	public $code;

	public function getIsShouldApply() {
		return $this->matchUsers();
	}
	
	public function matchUsers() {
		return ArrayHelper::isIn(\Yii::$app->user->id, $this->users);
	}
	
	public function getDiscountForCartItem($cartItem) {
		return $this->isShouldApply ? $cartItem->unit_price * $this->discount_percent / 100 + $this->discount_amount : 0;
	}
	
	public function getDiscountForCart($cart) {
		return $this->isShouldApply ? $cart->total * $this->discount_percent / 100 + $this->discount_amount : 0;
	}
}