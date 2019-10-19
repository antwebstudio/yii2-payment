<?php
//namespace tests\codeception\common\payment;
//use tests\codeception\common\UnitTester;
use ant\payment\models\Payment;
use ant\payment\models\Invoice;
use ant\payment\models\InvoiceItem;

class PaymentCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function testSave(UnitTester $I)
    {
        $invoice = $this->createInvoice();

        $payment = new Payment;
        $payment->attributes = [
            'payment_gateway' => 'testMethod',
            'invoice_id' => $invoice->id,
            'amount' => '1, 521.10',
            'currency' => 'MYR',
            'data' => ['data' => 'test'],
            'status' => 0,
        ];
        
        if (!$payment->save()) throw new \Exception(print_r($payment->errors,1));
        $I->assertTrue($payment->save());
    }

    protected function createInvoice() {
        $model = new Invoice;
        $model->attributes = [
            'total_amount' => 0,
            'issue_to' => 0,
        ];
        if (!$model->save()) throw new \Exception(print_r($model->errors,1));

        $item = new InvoiceItem;
        $item->attributes = [
            'invoice_id' => $model->id,
            'title' => 'test item',
            'unit_price' => 1521.10,
        ];
        if (!$item->save()) throw new \Exception(print_r($item->errors,1));

        $model->total_amount = $item->unit_price;
        if (!$model->save()) throw new \Exception(print_r($model->errors,1));

        return $model;
    }
}
