<?php
namespace ant\payment\interfaces;

interface PayableItem extends \ant\payment\interfaces\BillableItem
{
	public function deductAvailableQuantity($quantity);
}