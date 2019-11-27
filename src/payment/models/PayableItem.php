<?php
namespace ant\payment\models;

interface PayableItem extends \ant\payment\interfaces\BillableItem
{
	public function deductAvailableQuantity($quantity);
}