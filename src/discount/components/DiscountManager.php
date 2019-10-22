<?php
namespace ant\discount\components;

use Yii;
use ant\discount\helpers\Discount;
use ant\discount\models\DiscountRule;

class DiscountManager extends \yii\base\Component {
	
	public $overrideMethods = [];
	public $rules;
	
	public function getRules() {
		if (!isset($this->rules)) {
			$this->rules = \ant\discount\models\DiscountRule::find()->all();
		}
		$rules = [];
		foreach ($this->rules as $rule) {
			if ($rule instanceof \ant\discount\models\DiscountRule) {
				$rules[] = Yii::createObject([
					'class' => $rule->class,
					'discount_percent' => $rule->discount_percent,
					'users' => $rule->user_ids,
					'products' => $rule->product_ids,
					'categories' => $rule->category_ids,
				]);
			} else {
				$rules[] = Yii::createObject($rule);
			}
		}
		return $rules;
	}
	
	public function getDiscountForForm($formModel) {
		
		if (isset($this->overrideMethods['getDiscountForForm']) && is_callable($this->overrideMethods['getDiscountForForm'])) {
			return call_user_func_array($this->overrideMethods['getDiscountForForm'], [$formModel]);
		}
		
		return Discount::percent($this->_getDiscountForItem($formModel->item, $formModel->quantity) * 100);
	}
	
	public function getDiscountAmountForForm($formModel) {
		
	}
	
	public function getDiscountAmountForItem($item, $price, $quantity) {
		$rate = $this->getDiscountForItem($item, $quantity);
		return $price * $rate;
	}
	
	// Return value > 0 for percentage discount, return value < 0 for amount discount
	protected function _getDiscountForItem($item, $quantity) {
		if (isset($this->overrideMethods['getDiscountAmountForItem']) && is_callable($this->overrideMethods['getDiscountAmountForItem'])) {
			return call_user_func_array($this->overrideMethods['getDiscountAmountForItem'], [$item, $quantity]);
		}
		if (!\Yii::$app->user->isGuest) {
			$percentage = \Yii::$app->user->identity->getDynamicAttribute('discountRate');
			
			$rules = DiscountRule::find()->andWhere(['class' => \ant\discount\rule\CatalogRule::className()])->all();
			
			if (isset($rules)) {
				return $percentage / 100;
			}
		}
	}
	
	public function getDiscountForCart($cart) {
		if (isset($this->overrideMethods['getDiscountForCart']) && is_callable($this->overrideMethods['getDiscountForCart'])) {
			return call_user_func_array($this->overrideMethods['getDiscountForCart'], [$cart]);
		}
		
		$total = 0;
		foreach ($this->getRules() as $rule) {
			$total += $rule->getDiscountForCart($cart);
		}
		return Discount::amount($total);
	}
	
	public function getDiscountForCartItem($cartItem) {
		if (isset($this->overrideMethods['getDiscountForCartItem']) && is_callable($this->overrideMethods['getDiscountForCartItem'])) {
			return call_user_func_array($this->overrideMethods['getDiscountForCartItem'], [$cartItem]);
		}
		
		$total = 0;
		foreach ($this->getRules() as $rule) {
			$total += $rule->getDiscountForCartItem($cartItem);
		}
		return Discount::amount($total);
	}
}