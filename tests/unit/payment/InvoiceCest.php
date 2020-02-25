<?php
use ant\discount\helpers\Discount;
use ant\payment\models\Invoice;
use ant\payment\models\InvoiceItem;
use ant\payment\models\Payable;
use ant\payment\models\Billable;
use ant\payment\models\Payment;
use ant\payment\models\BillableItem;
use ant\payment\components\PaymentMethod;
use yii\helpers\Html;

class InvoiceCest
{
	protected $_default;
	
    public function _before(UnitTester $I)
    {
		\Yii::configure(\Yii::$app, [
            'components' => [
				'discount' => [
					'class' => 'ant\discount\components\DiscountManager',
					'rules' => [],
				],
				'cart' => [
					'class' => 'ant\cart\components\CartManager',
					'types' => [
						'default' => [
							'item' => function() {
								
							}
						],
					],
				],
			],
		]);
		
		$this->_default = [
			InvoiceCestBillableModel::className() => [
				'firstname' => 'firstname',
				'lastname' => 'lastname',
				'email' => 'example@example.org',
				'contact' => '0164601234',
			],
		];
    }

    public function _after(UnitTester $I)
    {
    }
	
    public function _fixtures()
    {
        return [
            'user' => [
                'class' => \tests\fixtures\UserFixture::className(),
            ],
            'contact' => [
                'class' => \tests\fixtures\ContactFixture::className(),
            ],
        ];
    }

    // Each year id will continue
    public function testFormattedId(UnitTester $I)
    {
    	//$module = \Yii::$app->getModule('payment');
		//$I->assertTrue(isset($module));
    	//$module->invoiceNumberFormat = 'CW{date:y}{id:4}';
		
		$year = 2015;
		
    	$invoice = new Invoice();
		
		$invoice->attachBehavior('formattedAutoColumn', [
			'class' => \ant\behaviors\FormattedAutoIncreaseColumnBehavior::className(),
			'format' => 'CW{date:y}{id:4}',
			'saveToAttribute' => 'formatted_id',
			'createdDateAttribute' => 'issue_date',
		]);
		$invoice->total_amount = 100;
    	$invoice->issue_to = 1;
		$invoice->issue_date = $year.'/1/1';
    	
    	if (!$invoice->save()) throw new \Exception(Html::errorSummary($invoice));
    	
    	$expected = 'CW'.substr($year, 2).str_pad($invoice->id, 4, 0, STR_PAD_LEFT);
    	
    	$I->assertEquals($expected, $invoice->reference);
		
	}
	
	// Each year will start from 0001
	public function testFormattedId2(UnitTester $I) {
		$format = 'CW{date:y}{year-id:4}';

    	//$module = \Yii::$app->getModule('payment');
		// ID by year
		//$module->invoiceNumberFormat = 'CW{date:y}{year-id:4}';		

		$year = 1971;
		$nextId = Invoice::find()->issuedAtYear($year)->count() + 1;
		
		$invoice = new Invoice();
		$invoice->attachBehavior('formattedAutoColumn', [
			'class' => \ant\behaviors\FormattedAutoIncreaseColumnBehavior::className(),
			'format' => $format,
			'saveToAttribute' => 'formatted_id',
			'createdDateAttribute' => 'issue_date',
		]);
		$invoice->total_amount = 100;
    	$invoice->issue_to = 1;
		$invoice->issue_date = $year.'/1/1';
    	
    	if (!$invoice->save()) throw new \Exception(Html::errorSummary($invoice));
    	
    	$expected = 'CW'.substr($year, 2).str_pad($nextId, 4, 0, STR_PAD_LEFT);
    	
    	$I->assertEquals($expected, $invoice->reference);
	
		// Second year
		$year = 1972;
		$nextId = Invoice::find()->issuedAtYear($year)->count() + 1;
		
		$invoice = new Invoice();
		$invoice->attachBehavior('formattedAutoColumn', [
			'class' => \ant\behaviors\FormattedAutoIncreaseColumnBehavior::className(),
			'format' => $format,
			'saveToAttribute' => 'formatted_id',
			'createdDateAttribute' => 'issue_date',
		]);
		$invoice->total_amount = 100;
    	$invoice->issue_to = 1;
		$invoice->issue_date = $year.'/1/1';
    	
    	if (!$invoice->save()) throw new \Exception(Html::errorSummary($invoice));
    	
    	$expected = 'CW'.substr($year, 2).str_pad($nextId, 4, 0, STR_PAD_LEFT);
    	
    	$I->assertEquals($expected, $invoice->reference);
		
		// Second year second
		
		$invoice = new Invoice();
		$invoice->attachBehavior('formattedAutoColumn', [
			'class' => \ant\behaviors\FormattedAutoIncreaseColumnBehavior::className(),
			'format' => $format,
			'saveToAttribute' => 'formatted_id',
			'createdDateAttribute' => 'issue_date',
		]);
		$invoice->total_amount = 100;
    	$invoice->issue_to = 1;
		$invoice->issue_date = $year.'/1/1';
    	
    	if (!$invoice->save()) throw new \Exception(Html::errorSummary($invoice));
    	
    	$expected = 'CW'.substr($year, 2).str_pad($nextId + 1, 4, 0, STR_PAD_LEFT);
    	
    	$I->assertEquals($expected, $invoice->reference);
    }
	
	public function testGetServiceCharges(UnitTester $I) {
		$serviceCharges = 3.12;
		
		$invoice = new Invoice;
		$invoice->total_amount = 100;
		$invoice->service_charges_amount = $serviceCharges;
		$invoice->issue_to = 1;
		
		$item = new InvoiceItem;
		$item->title = 'test';
		$item->unit_price = 100;
		
		if (!$invoice->save()) throw new \Exception(Html::errorSummary($invoice));
		
		$invoice->link('paymentInvoiceItems', $item);
		
		$I->assertEquals($serviceCharges, $invoice->getServiceCharges());
	}
	
	public function testCreateFromBillableModel(UnitTester $I) {
		$payable = new InvoiceCestTestModel;
		$invoice = Invoice::createFromBillableModel($payable);
		
		$I->assertEquals($invoice->getCalculatedTotalAmount(), $invoice->total_amount);
		
		$I->assertEquals(2.03, $invoice->total_amount);
		$I->assertEquals(0, $invoice->discountAmount);
		$I->assertEquals(0, $invoice->tax_amount);
		$I->assertEquals(0, $invoice->paid_amount);
		$I->assertEquals(null, $invoice->issue_to);
		$I->assertEquals(Invoice::STATUS_ACTIVE, $invoice->status);
		$I->assertEquals(1.03, $invoice->service_charges_amount);
	}
	
	public function testCreateFromBillableModelWithItemDiscount(UnitTester $I) {
		$payable = new InvoiceCestTestModelWithItemDiscount;
		
		$I->assertEquals(1.95, $payable->getNetTotal());
		
		$invoice = Invoice::createFromBillableModel($payable);
		$I->assertEquals($invoice->getCalculatedTotalAmount(), $invoice->total_amount);
		
		$I->assertEquals(1.95, $invoice->total_amount);
		$I->assertEquals(0, $invoice->discountAmount); // This discountAmount is invoice discount, not total of invoice item discount amount
		$I->assertEquals(0, $invoice->tax_amount);
		$I->assertEquals(0, $invoice->paid_amount);
		$I->assertEquals(null, $invoice->issue_to);
		$I->assertEquals(Invoice::STATUS_ACTIVE, $invoice->status);
		$I->assertEquals(1.03, $invoice->service_charges_amount);
		
		$I->assertEquals(10, $invoice->billItems[0]->discount_value);
		$I->assertEquals(1, $invoice->billItems[0]->discount_type);
	}
	
	public function testcreateFromBillableModelWithDiscount(UnitTester $I) {
		$payable = new InvoiceCestTestModelWithDiscount;
		$invoice = Invoice::createFromBillableModel($payable);
		
		$I->assertEquals($invoice->getCalculatedTotalAmount(), $invoice->total_amount);
		
		$I->assertEquals(1.03, $invoice->total_amount);
		$I->assertEquals(1, $invoice->discount_amount);
		$I->assertEquals(0, $invoice->tax_amount);
		$I->assertEquals(0, $invoice->paid_amount);
		$I->assertEquals(null, $invoice->issue_to);
		$I->assertEquals(Invoice::STATUS_ACTIVE, $invoice->status);
		$I->assertEquals(1.03, $invoice->service_charges_amount);
	}
	
	public function testCreateFromBillableModelWhereAmountWrong(UnitTester $I) {
		$exceptionThrown = false;
		$payable = new InvoiceCestTestModel2;
		$countBefore = Invoice::find()->count();
		
		try {
			$invoice = Invoice::createFromBillableModel($payable);
		} catch (\Exception $ex) {
			$exceptionThrown = true;
		}
		
		$countAfter = Invoice::find()->count();
		
		$I->assertTrue($exceptionThrown);
		$I->assertEquals($countAfter, $countBefore);
	}
	
	public function testGetBilledTo(UnitTester $I) {
		$data = [
			'contact_name' => 'test contact',
			'email' => 'test@gmail.com',
			'contact_number' => '0161234567',
		];
		
		$contact = $this->createContact($data);
		
		$payable = new InvoiceCestTestModel;
		$invoice = Invoice::createFromBillableModel($payable, $contact);
		
		$I->assertEquals($data['contact_name'], $invoice->billedTo->contact_name);
		$I->assertEquals($data['email'], $invoice->billedTo->email);
		$I->assertEquals($data['contact_number'], $invoice->billedTo->contact_number);
	}
	
	public function testGetDueAmount(UnitTester $I) {
		$payable = new InvoiceCestTestModel;
		$invoice = Invoice::createFromBillableModel($payable);
		
		// Must remain 1.00 and 1.03, change to other value will not able to detect an issue caused when it is equal to 1.00 and 1.03
		// This line will return false (refer to Invoice::getDueAmount)
		// (int) $invoice->getCalculatedTotalAmount() != (int) $invoice->total_amount
		// But this line will return true
		// $invoice->getCalculatedTotalAmount() != $invoice->total_amount
		// This only happened when the value is 1.00 and 1.03
		// '1.00' + '1.03' == '2.03' // false
		// '1.00' + '1.02' == '2.02' // true
		$I->assertEquals('1.00', $invoice->getCalculatedSubtotal());
		$I->assertEquals('1.03', $invoice->getServiceCharges()); 
		$I->assertEquals($invoice->getDueAmount(), $invoice->total_amount);
	}
	
	public function testGetDueAmountAfterPay(UnitTester $I) {
		$payable = new InvoiceCestTestModel;
		$invoice = Invoice::createFromBillableModel($payable);
		$amount = $invoice->getDueAmount();

		$I->assertEquals($payable->netTotal, $amount);

		$invoice->pay($amount);

		$payment = new Payment;
		$payment->attributes = [
			'payment_gateway' => 'Test',
			'amount' => $amount,
			'invoice_id' => $invoice->id,
			'currency' => 'test',
			'data' => ['test'],
			'is_valid' => 1,
			'status' => PaymentMethod::STATUS_SUCCESS,
		];
		if (!$payment->save()) throw new \Exception(Html::errorSummary($payment));
		
		$I->assertEquals(0, $invoice->getDueAmount());
	}
	
    // tests
    public function testGetCalculatedTotalAmountWithRoudingIssue(UnitTester $I)
    {
		$data = [
			'unit_price' => 0.50,
			'discount_value' => 10,
			'discount_type' => 1,
			'quantity' => 1,
		];
		
		$invoice = new Invoice([
			'total_amount' => 1.93,
			'issue_to' => 0,
			'service_charges_amount' => 1.03,
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
		
		$invoiceItem2 = new InvoiceItem([
			'title' => 'test invoice item',
			'unit_price' => $data['unit_price'],
			'quantity' => $data['quantity'],
			'discount_value' => $data['discount_value'],
			'discount_type' => $data['discount_type'],
			'invoice_id' => $invoice->id,
		]);
		
		if (!$invoiceItem2->save()) throw new \Exception(Html::errorSummary($invoiceItem2));
		
		$invoice = Invoice::findOne($invoice->id);
		
		$I->assertEquals(1.93, $invoice->getCalculatedTotalAmount());
    }
	
    // tests
    public function testGetCalculatedTotalAmountWithRoudingIssue2(UnitTester $I)
    {
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
		
		$I->assertEquals(125.40, $invoice->getCalculatedTotalAmount());
    }
	
	public function testIsPaid(UnitTester $I) {
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
		
		$I->assertFalse($invoice->isFree);
		$I->assertFalse($invoice->isPaid);
		
		// @TODO: enhance integration of pay method with payment method
		$paymentMethod = new InvoiceCestTestPaymentMethod;
		$I->setProperty($paymentMethod, 'amount', 125.40);
		$record = $paymentMethod->savePaymentRecord($invoice);
		
		$invoice->pay(125.40);
		
		$I->assertTrue($invoice->isPaid); // Needed to avoid issue where invoice isPaid return false before the getPaidAmount is called.
		$I->assertSame('125.40', $invoice->getPaidAmount()); // Use assertSame and string for expected result for currency
		$I->assertTrue($invoice->isPaid);
	}
	
	public function testPay(UnitTester $I) {
		$user = $I->grabFixture('user')->getModel(0);
		$invoice = $this->createInvoice();
		
		$invoice->pay(10);
		$invoice->refresh();
		
		$I->assertEquals(10, $invoice->paid_amount);
	}

	public function testPayManually(UnitTester $I) {
		$amount = 125.40;

		$invoice = $this->createInvoice();

		$I->assertEquals($amount, $invoice->getDueAmount());
		$I->assertFalse($invoice->isPaid);

		// Pay part
		$invoice->payManually(100);

		$I->assertFalse($invoice->isPaid);
		$I->assertEquals(100, $invoice->paid_amount);
		$I->assertEquals(25.40, $invoice->getDueAmount());

		// Pay the rest due amount
		$invoice->payManually(25.40);
		
		$I->assertTrue($invoice->isPaid);
		$I->assertEquals($amount, $invoice->paid_amount);

		// Overpay
		$invoice->payManually($amount);
		//throw new \Exception($invoice->getCalculatedTotalAmount().' - '.$invoice->getPaidAmount().' = '. $invoice->getDueAmount());
		
		//throw new \Exception($I->invokeMethod($invoice, 'getCalculatedAmountNotIncludedInSubtotal') );
		//throw new \Exception($invoice->getCalculatedSubtotal() );
		$I->assertTrue($invoice->isPaid);
		$I->assertEquals($amount * 2, $invoice->paid_amount);
	}

	public function testRefundManually(UnitTester $I) {
		$amount = 125.40;
		$refundAmount = 100;

		$invoice = $this->createInvoice();

		$I->assertEquals($amount, $invoice->getDueAmount());
		$I->assertFalse($invoice->isPaid);
		
		// Make payment
		$paymentMethod = new InvoiceCestTestPaymentMethod;
		$I->setProperty($paymentMethod, 'amount', $amount + $refundAmount);
		$record = $paymentMethod->savePaymentRecord($invoice);

		$invoice->pay($amount + $refundAmount);

		$I->assertTrue($invoice->isPaid);
		$I->assertEquals($amount + $refundAmount, $invoice->paid_amount);

		// Refund
		$paymentMethod = new InvoiceCestTestPaymentMethod;
		$I->setProperty($paymentMethod, 'amount', - $refundAmount);
		$record = $paymentMethod->savePaymentRecord($invoice);

		$invoice->refundManually($refundAmount);

		$I->assertTrue($invoice->isPaid);
		$I->assertEquals($amount, $invoice->paid_amount);
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
	
	protected function create($class, $attributes = [], $config = []) {
		$model = new $class($config);
		$fullClassName = $model->className();
		$defaultAttributes = isset($this->_default[$fullClassName]) ? $this->_default[$fullClassName] : [];
		
		$model->attributes = \yii\helpers\ArrayHelper::merge($defaultAttributes, $attributes);
		
		if (!$model->save()) throw new \Exception($fullClassName . "\n" . \yii\helpers\Html::errorSummary($model));
		
		return $model;
	}
	
	protected function createContact($attributes) {
		$contact = new \ant\contact\models\Contact;
		$contact->attributes = \yii\helpers\ArrayHelper::merge([
			'contact_name' => 'test contact',
			'email' => 'test@gmail.com',
			'contact_number' => '0161234567',
		], $attributes);
		
		if (!$contact->save()) throw new \Exception(Html::errorSummary($contact));
		
		return $contact;
	}
}

class InvoiceCestTestModel extends \yii\base\Model implements Billable {
	public function getTaxCharges() {
		return 0;
	}

	public function getAbsorbedServiceCharges() {
		return 0;
	}
	
	public function getServiceCharges() {
		return 1.03;
	}
	
	public function getDueAmount() {
		return 1;
	}
	
	public function getCurrency() {
		return 'MYR';
	}
	/*
	public function getIsFree() {
		return false;
	}
	
	public function getIsPaid() {
		return false;
	}*/
	
	public function getNetTotal() {
		// 2 x item.amount + 2 x item.amount + serviceCharges = 0.50 + 0.50 + 1.12 = 2.12
		return 2.03;
	}
	
	public function getSubtotal() {
		return $this->getNetTotal();
	}
	
	public function getBillItems() {
		// 0.50 + 0.50
		return [new InvoiceCestTestModelPayableItem, new InvoiceCestTestModelPayableItem];
	}
	
	public function getDiscountAmount() {
		return 0;
	}
}

class InvoiceCestTestModelWithItemDiscount extends InvoiceCestTestModel {
	public function getNetTotal() {
		// 2 x item.amount + 2 x item.amount + serviceCharges = 0.46 + 0.46 + 1.03 = 1.95
		return 1.95;
	}
	public function getBillItems() {
		// 0.25 * 0.9 + 0.25 * 0.9
		// = 0.46 + 0.46
		return [new InvoiceCestTestModelPayableItemWithDiscount, new InvoiceCestTestModelPayableItemWithDiscount];
	}
}

class InvoiceCestTestModelWithDiscount extends InvoiceCestTestModel {
	public function getDiscountAmount() {
		return 1;
	}
	
	public function getNetTotal() {
		return 1.03;
	}
}

class InvoiceCestTestModel2 extends InvoiceCestTestModel {
	public function getNetTotal() {
		// Return wrong value
		return 4.12;
	}
}

class InvoiceCestTestModelPayableItem extends \yii\base\Model implements BillableItem {
	use \ant\payment\traits\BillableItemTrait;
	//public $discount;
	
	public function getDiscount() {
		return Discount::amount(0);
	}
	
	public function setDiscount($discount, $discountType = 0) {
		
	}
	
    public function getAmount() {
		return $this->getNetTotal();
	}
	
	public function getUnitPrice() {
		return 0.25;
	}
	
	public function getDiscountedUnitPrice() {
		return $this->discount->of($this->getUnitPrice());
	}
	
	public function getDescription() {
		return 'test description';
	}
	
	public function getNetTotal() {
		return $this->getDiscountedUnitPrice() * $this->getQuantity();
	}

    public function getQuantity() {
		return 2;
	}

    public function getId() {
		return 1;
	}

    public function getTitle() {
		return 'test item';
	}
	
	public function deductAvailableQuantity($quantity) {
		
	}
}

class InvoiceCestTestModelPayableItemWithDiscount extends InvoiceCestTestModelPayableItem {
	public function getDiscount() {
		return Discount::percent(10);
	}
}

class InvoiceCestTestPaymentMethod extends PaymentMethod {
	protected $amount;

	public function initGateway() {
		
	}
	
	public function getPurchaseRequestAmountParamName() {
		
	}
	
	public function getPaymentRecordData() {
		return [
			'amount' => $this->amount,
			'ref_no' => '1',
			'currency' => 'MYR',
			'status' => PaymentMethod::STATUS_SUCCESS,
			'is_valid' => 1,
			'signature' => '',
			'remark' => '',
			'merchant_code' => '',

			//'data' => is_array($data) ? json_encode($data) : $data,
		];
	}
	
	public function getPaymentRecord() {
		$payment = new \ant\payment\models\Payment([
			'transaction_id' => 'TEST_'.uniqid(),
			'payment_gateway' => 'testPaymentGateway',
			'data' => 'test',
		]);
		Yii::configure($payment, $this->getPaymentRecordData());
		
		return $payment;
	}
}

class InvoiceCestBillableModel extends \yii\db\ActiveRecord implements \ant\payment\models\Billable {
	public function getDiscountAmount() {
		
	}
    
    public function getAbsorbedServiceCharges() {
		
	}
    
	public function getServiceCharges() {
		
	}
	
	public function getTaxCharges() {
		
	}

    public function getCurrency() {
		
	}
	
    public function getDueAmount() {
		
	}
	
    public function getSubtotal() {
		
	}
	
    public function getNetTotal() {
		
	}

    public function getBillItems() {
		
	}
}