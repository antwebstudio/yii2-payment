<?php
namespace ant\payment\components;

interface PaymentMethodInterface
{
	public function initGateway();
	
	public function getPaymentRecordData();
	
	public function getPurchaseRequestAmountParamName();
	
	public function getPaymentDataForGateway($payable);
}