<?php
namespace common\modules\payment\models;

interface BillableItem
{
	public function getUnitPrice();
	
	public function getDiscountedUnitPrice();

    public function getQuantity();
	
	public function getDescription();

    public function getId();

    public function getTitle();
	
	public function setDiscount($discount, $discountType = 0);
	
	public function getDiscount();
	
	public function getIncludedInSubtotal();
}