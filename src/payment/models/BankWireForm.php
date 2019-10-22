<?php
namespace ant\payment\models;

use yii\helpers\Html;
use ant\helpers\DateTime;
use ant\payment\models\Payment;
use ant\payment\components\PaymentMethod;

class BankWireForm extends \ant\base\FormModel {
    const SCENARIO_ATTACHMENT_ONLY = 'attachment_only';

    public $paymentMethod = 'ant\payment\components\BankWirePaymentMethod';
    public $paymentSuccessMessage = 'Credit topup detail submitted succesfully, please allow a few working days for our admin to verify your submission. ';
    //public $paymentSuccessRoute = ['index'];
    public $bank;
    public $amount;
    public $date;
    public $time;
    public $reference;
    public $attachment;

    public $currency = 'MYR';

    protected $_payment;
    protected $_invoice;

    public function rules() {
        return [
            [['attachment'], 'required'],
            [['bank', 'amount', 'reference'], 'required', 'except' => [self::SCENARIO_ATTACHMENT_ONLY]],
            [['bank', 'amount', 'date', 'time', 'reference', 'attachment'], 'safe'],
            [['amount'], 'number'],
        ];
    }

    public function beforeCommit() {
        if (!isset($this->_payment)) {
            $this->_payment = new Payment;
            $this->_payment->attributes = [
                'attachments' => [$this->attachment],
                'payment_gateway' => $this->paymentMethod,
                'transaction_id' => $this->reference,
                'amount' => $this->amount,
                'paid_at' => $this->getDateTime(),
                'merchant_code' => $this->bank,
                'data' => ['test'],
                'currency' => $this->currency,
                'invoice_id' => $this->invoice->id,
                'status' => PaymentMethod::STATUS_PENDING,
            ];

            if (!$this->_payment->save()) {
                $this->addErrors($this->_payment->errors);
                throw new \Exception(print_r($this->_payment->errors, 1));
            }
        }
        $this->sendNotificationEmailToAdmin();
        return true;
    }

    public function getPayment() {
        return $this->_payment;
    }

    public function setInvoice($invoice) {
        $this->_invoice = Invoice::findOne($invoice);
    }

    public function getInvoice($autoCreate = true) {
        if (!isset($this->_invoice) && $autoCreate) {
            $this->_invoice = new Invoice;
            $this->_invoice->total_amount = $this->amount;
            $this->_invoice->issue_to = 0;
            if (!$this->_invoice->save()) throw new \Exception(Html::errorSummary($this->_invoice));

            $item = new InvoiceItem(['title' => 'Topup credit']);
            $item->unit_price = $this->amount;
            $item->invoice_id = $this->_invoice->id;
        
            if (!$item->save()) throw new \Exception(Html::errorSummary($item));
        }
        return $this->_invoice;
    }

    public function getDateTime() {
        $date = new DateTime($this->date.' '.$this->time);
        return $date->format(DateTime::FORMAT_MYSQL);
    }

    public function sendNotificationEmailToAdmin() {
        $to = env('ADMIN_EMAIL');
        return \Yii::$app->mailer->compose('payment/bank-wire-slip', [
            'payment' => $this->payment,
            'invoice' => $this->invoice,
        ])
        ->setFrom([env('ROBOT_EMAIL') => \Yii::$app->name])
        ->setTo(YII_DEBUG ? env('DEVELOPER_EMAIL') : $to)
        ->setSubject('Payment slip for invoice: ' . $this->invoice->reference)
        ->send();
    }
}