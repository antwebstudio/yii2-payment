<?php
namespace ant\payment\models;

interface PayableItem extends \ant\payment\models\BillableItem
{
	public function deductAvailableQuantity($quantity);
}