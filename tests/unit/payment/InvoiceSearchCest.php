<?php 
use common\modules\payment\models\Invoice;
use common\modules\payment\models\InvoiceItem;
use common\modules\payment\models\InvoiceSearch;

class InvoiceSearchCest
{
    public function _before(UnitTester $I)
    {
    }

    // tests
    public function testSearch(UnitTester $I)
    {
		$this->createInvoice();
		
    	$searchModel = new InvoiceSearch();
        $dataProvider = $searchModel->search([]);
		
		$invoices = $dataProvider->query->all();
    }

	protected function createInvoice() {
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
		
		if (!$invoiceItem->save()) throw new \Exception(Html::errorSummary($invoiceItem));
		
		return $invoice;
	}
}
