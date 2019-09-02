<?php
namespace common\modules\payment\models;

class Order extends \common\modules\order\models\Order {
	public function init() {
		if (YII_DEBUG) throw new \Exception('DEPRECATED');
		return parent::init();
	}
}