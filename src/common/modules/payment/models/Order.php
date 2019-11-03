<?php
namespace common\modules\payment\models;

class Order extends \ant\order\models\Order {
	public function init() {
		if (YII_DEBUG) throw new \Exception('DEPRECATED');
		return parent::init();
	}
}