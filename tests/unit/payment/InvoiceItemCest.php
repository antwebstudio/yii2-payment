<?php
use yii\helpers\Html;
use ant\cart\models\CartItem;
use ant\payment\models\Invoice;
use ant\payment\models\InvoiceItem;

class InvoiceItemCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }
	
	public function testGetDiscountedUnitPrice(UnitTester $I) {
		$data = [
			'unit_price' => 69.6667,
			'discount_value' => 10,
			'discount_type' => 1,
			'quantity' => 2,
		];
		
		$invoice = new Invoice([
			'total_amount' => 125.40,
			'issue_to' => 0,
		]);
		
		if (!$invoice->save()) throw new \Exception(Html::errorSummary($invoice));
		
		$invoiceItem = new InvoiceItem([
			'title' => 'test invoice item',
			'unit_price' => $data['unit_price'],
			'quantity' => $data['quantity'],
			'discount_value' => $data['discount_value'],
			'discount_type' => $data['discount_type'],
			'invoice_id' => $invoice->id,
		]);
		
		$I->assertEquals(62.70, $invoiceItem->discountedUnitPrice);
		
		if (!$invoiceItem->save()) throw new \Exception(Html::errorSummary($invoiceItem));
		
		$I->assertEquals(62.70, $invoiceItem->discountedUnitPrice);
		
		$invoiceItem = InvoiceItem::findOne($invoiceItem->id);
		
		$I->assertEquals(62.70, $invoiceItem->discountedUnitPrice);
	}
	
	public function testGetNetTotal(UnitTester $I) {
		$data = [
			'unit_price' => 69.6667,
			'discount_value' => 10,
			'discount_type' => 1,
			'quantity' => 2,
		];
		
		$invoice = new Invoice([
			'total_amount' => 125.40,
			'issue_to' => 0,
		]);
		
		if (!$invoice->save()) throw new \Exception(Html::errorSummary($invoice));
		
		$invoiceItem = new InvoiceItem([
			'title' => 'test invoice item',
			'unit_price' => $data['unit_price'],
			'quantity' => $data['quantity'],
			'discount_value' => $data['discount_value'],
			'discount_type' => $data['discount_type'],
			'invoice_id' => $invoice->id,
		]);
		
		$I->assertEquals(125.40, $invoiceItem->netTotal);
		
		if (!$invoiceItem->save()) throw new \Exception(Html::errorSummary($invoiceItem));
		
		$I->assertEquals(125.40, $invoiceItem->netTotal);
		
		$invoiceItem = InvoiceItem::findOne($invoiceItem->id);
		
		$I->assertEquals(125.40, $invoiceItem->netTotal);
	}
}
