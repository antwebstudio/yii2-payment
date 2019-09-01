<?php
namespace common\modules\payment\components;

interface PaymentMethodInterface
{
	public function initGateway();
	
	public function getPaymentRecordData();
	
	public function getPurchaseRequestAmountParamName();
}
?>
