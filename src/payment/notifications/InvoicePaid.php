<?php
namespace ant\payment\notifications;

use tuyakhov\notifications\NotificationInterface;
use tuyakhov\notifications\NotificationTrait;

class InvoicePaid implements NotificationInterface {
    use NotificationTrait;

    public $invoice;

    public function __construct($invoice) {
        $this->invoice = $invoice;
    }

    public function exportForMail() {
        return \Yii::createObject([
           'class' => '\tuyakhov\notifications\messages\MailMessage',
           'subject' => 'Receipt for payment',
           'view' => ['html' => '@ant/payment/mails/invoice-paid'],
           'viewData' => [
               'invoice' => $this->invoice,
           ]
        ]);
    }
}