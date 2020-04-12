<?php
namespace ant\payment\components;

use Yii;
use ant\payment\components\PaymentMethod;
use ant\payment\models\Invoice;

class CashOnDeliveryPaymentMethod extends PaymentMethod
{
	public function initGateway() {
		//$this->_gateway = new BankWireGateway;
	}
	
	public function getPurchaseRequestAmountParamName() {
		//return 'amount';
    }
	
	public function getPaymentRecordData() {
        return [
		];
    }
}