<?php
namespace ant\payment\models\query;

class PaymentQuery extends \yii\db\ActiveQuery {
	public function behaviors() {
		return [
			\ant\behaviors\AttachBehaviorBehavior::class,
		];
	}
}