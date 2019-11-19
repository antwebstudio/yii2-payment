<?php
namespace ant\payment\events;

class PaymentEvent extends \yii\base\Event {
	
    /**
     * @var Model with payable interface
     */
    public $payable;
	
	public $response;
	
	public $invoice;
}