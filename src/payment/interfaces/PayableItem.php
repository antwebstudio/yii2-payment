<?php
namespace ant\payment\interfaces;

interface PayableItem extends \ant\payment\models\BillableItem
{
	public function deductAvailableQuantity($quantity);
}