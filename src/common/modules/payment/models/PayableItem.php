<?php
namespace common\modules\payment\models;

interface PayableItem extends \common\modules\payment\models\BillableItem
{
	public function deductAvailableQuantity($quantity);
}
?>
