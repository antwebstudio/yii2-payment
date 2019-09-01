<?php
namespace common\modules\payment\notifications;

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
           'view' => ['html' => '@common/modules/payment/mails/invoice-paid'],
           'viewData' => [
               'invoice' => $this->invoice,
           ]
        ]);
    }
}