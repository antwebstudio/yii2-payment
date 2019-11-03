<?php
namespace common\modules\discount\rule;

class CatalogRule extends DiscountRule implements \common\modules\discount\components\DiscountRuleInterface {
	public $products;
	public $categories;
	
	public $priority = 20;

	public function getIsShouldApply() {
		
	}
}