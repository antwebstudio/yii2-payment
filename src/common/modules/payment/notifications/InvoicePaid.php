<?php
namespace ant\payment\notifications;

use tuyakhov\notifications\NotificationInterface;
use tuyakhov\notifications\NotificationTrait;

class InvoicePaid implements \ant\payment\notifications\InvoicePaid {

    public function __construct($invoice) {
		if (YII_DEBUG) throw new \Exception('DEPRECATED');
		
        parent::__construct($invoice);
    }
}