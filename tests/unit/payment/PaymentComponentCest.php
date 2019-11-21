<?php 
use ant\payment\models\Invoice;
use ant\payment\models\Payment;
use ant\payment\interfaces\Payable;
use ant\payment\interfaces\Billable;
use ant\payment\interfaces\PayableItem;
use ant\payment\interfaces\BillableItem;
use ant\payment\components\PaymentComponent;

class PaymentComponentCest
{
    public function _before(UnitTester $I)
    {
    }
	
    public function _fixtures()
    {
        return [
            'invoice' => [
                'class' => \tests\fixtures\InvoiceFixture::className(),
                'dataFile' => '@tests/fixtures/data/invoice.php'
            ],
            'invoiceItem' => [
                'class' => \tests\fixtures\InvoiceItemFixture::className(),
                'dataFile' => '@tests/fixtures/data/invoice_item.php'
            ],
        ];
    }

    // tests
    public function testCompletePayment(UnitTester $I)
    {
		$_POST['PaymentId'] = 2;
		$_POST['RefNo'] = '2';
		$_POST['Amount'] = 10;
		$_POST['Currency'] = 'MYR';
		$_POST['Status'] = 1;
		$_POST['Signature'] = 'R36v/5gUqO+exaW80Za4IQpumE8=';
		$_POST['Remark'] = '';
		$_POST['TransId'] = 'TEST-1';
		
		$invoice = $I->grabFixture('invoice')->getModel(0);
		
		$payable = Yii::$app->payment->getPayableModel('invoice', $invoice->id);
		$gateway = Yii::$app->payment->getPaymentMethod('ipay88', $payable);
		
		$response = Yii::$app->payment->completePayment($gateway, $payable);
		
		$invoice = Invoice::findOne($invoice->id);
		
		//throw new \Exception($I->renderDbTable('{{%payment}}', ['transaction_id', 'invoice_id', 'amount']));
		
		$I->assertEquals(10, $invoice->paid_amount);
    }
	
	public function testCompletePaymentWithNotInvoice(UnitTester $I)
    {
		$_POST['PaymentId'] = 2;
		$_POST['RefNo'] = '2';
		$_POST['Amount'] = 10;
		$_POST['Currency'] = 'MYR';
		$_POST['Status'] = 1;
		$_POST['Signature'] = 'R36v/5gUqO+exaW80Za4IQpumE8=';
		$_POST['Remark'] = '';
		$_POST['TransId'] = 'TEST-1';
		
		$invoice = null;
		Yii::$app->payment->on(PaymentComponent::EVENT_PAYMENT_SUCCESS, function($event) use (&$invoice) {
			$invoice = $event->invoice;
		});
		
		$payable = $this->getPayable();
		$gateway = Yii::$app->payment->getPaymentMethod('ipay88', $payable);
		$response = Yii::$app->payment->completePayment($gateway, $payable);
		
		//$payable = $this->getPayable();
		//$gateway = Yii::$app->payment->getPaymentMethod('ipay88', $payable);
		//$response = Yii::$app->payment->completePayment($gateway, $payable);
		
		$invoice = Invoice::findOne($invoice->id);
		
		//throw new \Exception($invoice->id.':'.$invoice->paid_amount.':'.$I->renderDbTable('{{%payment}}', ['transaction_id', 'invoice_id', 'amount']));
		
		$I->assertEquals(10, $invoice->paid_amount);
		$I->assertTrue($payable->paid);
    }
	
	public function testCompletePaymentWithExceptionAtPaymentSuccess(UnitTester $I)
    {
		$_POST['PaymentId'] = 2;
		$_POST['RefNo'] = '2';
		$_POST['Amount'] = 10;
		$_POST['Currency'] = 'MYR';
		$_POST['Status'] = 1;
		$_POST['Signature'] = 'R36v/5gUqO+exaW80Za4IQpumE8=';
		$_POST['Remark'] = '';
		$_POST['TransId'] = 'TEST-1';
		
		$count = Invoice::find()->count();
		$countPayment = Payment::find()->count();
		
		$payable = new TestPayableWithException2;
		if (!$payable->save()) throw new \Exception(print_r($payable->errors, 1));
		$gateway = Yii::$app->payment->getPaymentMethod('ipay88', $payable);
		
		$exceptionThrown = false;
		try {
			$response = Yii::$app->payment->completePayment($gateway, $payable);
		} catch (\Exception $ex) {
			$exceptionThrown = true;
		}
		
		try {
			$response = Yii::$app->payment->completePayment($gateway, $payable);
		} catch (\Exception $ex) {
			$exceptionThrown = true;
		}
		
		//throw new \Exception($invoice->id.':'.$invoice->paid_amount.':'.$I->renderDbTable('{{%payment}}', ['transaction_id', 'invoice_id', 'amount']));
		
		$I->assertTrue($exceptionThrown);
		$I->assertEquals($count + 1, Invoice::find()->count());
		$I->assertEquals($countPayment + 1, Payment::find()->count());
		//$I->assertTrue($payable->paid);
    }
	
	public function testCompletePaymentWithExceptionAtCreatingInvoice(UnitTester $I)
    {
		$_POST['PaymentId'] = 2;
		$_POST['RefNo'] = '2';
		$_POST['Amount'] = 10;
		$_POST['Currency'] = 'MYR';
		$_POST['Status'] = 1;
		$_POST['Signature'] = 'R36v/5gUqO+exaW80Za4IQpumE8=';
		$_POST['Remark'] = '';
		$_POST['TransId'] = 'TEST-1';
		
		$count = Invoice::find()->count();
		$countPayment = Payment::find()->count();
		
		$payable = new TestPayableWithException;
		if (!$payable->save()) throw new \Exception(print_r($payable->errors, 1));
		$gateway = Yii::$app->payment->getPaymentMethod('ipay88', $payable);
		
		$exceptionThrown = false;
		try {
			$response = Yii::$app->payment->completePayment($gateway, $payable);
		} catch (\Exception $ex) {
			$exceptionThrown = true;
		}
		
		// Data returned should be recorded even the payment is failed.
		$I->assertEquals($countPayment + 1, Payment::find()->count());
		
		try {
			$response = Yii::$app->payment->completePayment($gateway, $payable);
		} catch (\Exception $ex) {
			$exceptionThrown = true;
		}
		
		$I->setProperty($payable, 'throwException', false);
		$response = Yii::$app->payment->completePayment($gateway, $payable);
		
		//throw new \Exception($invoice->id.':'.$invoice->paid_amount.':'.$I->renderDbTable('{{%payment}}', ['transaction_id', 'invoice_id', 'amount']));
		
		$payment = Payment::findOne(['invoice_id' => $payable->invoice->id]);
		
		$I->assertTrue($exceptionThrown);
		$I->assertTrue(isset($payment));
		$I->assertEquals($count + 1, Invoice::find()->count());
		$I->assertEquals($countPayment + 1, Payment::find()->count());
		//$I->assertTrue($payable->paid);
    }
	
	protected function getPayable() {
		$payable = new TestPayable;
		if (!$payable->save()) throw new \Exception(print_r($payable->errors, 1));
		return $payable;
	}
}

class TestPayableItem extends \yii\base\Component implements BillableItem {
	public function getUnitPrice() {
		return 10;
	}
	
	public function getDiscountedUnitPrice() {
		return 0;
	}

    public function getQuantity() {
		return 1;
	}
	
	public function getDescription() {
		return '';
	}

    public function getId() {
		return 1;
	}

    public function getTitle() {
		return 'test item';
	}
	
	public function setDiscount($discount, $discountType = 0) {
		
	}
	
	public function getDiscount() {
		
	}
	
	public function getIncludedInSubtotal() {
		
	}
}

class TestPayable extends \yii\db\ActiveRecord implements Payable, Billable {
	use \ant\payment\traits\BillableActiveRecordTrait;
	
	public $paid = false;
	
	public static function tableName() {
		return '{{%test_payable}}';
	}
	
	public function behaviors() {
		return [
			[
				'class' => \ant\behaviors\EventHandlerBehavior::className(),
				'events' => [
					//Billable::EVENT_AFTER_BILL_SUCCESS => [$this, 'invoiceCreatedCallBack'],
					Payable::EVENT_AFTER_PAYMENT_SUCCESS => [$this, 'paymentSuccessCallBack'],
				],
			],
		];
	}
	
	public function invoiceCreatedCallBack($event) {
		throw new \Exception('invoiceCreatedCallBack Error');
	}
	
	public function paymentSuccessCallBack() {
		$this->paid = true;
	}
	
	public function getReference() {
		return 1;
	}
	
	public function pay($amount) {
	}
	
	//public function getServiceCharges();

    public function getCurrency() {
		return 'MYR';
	}

    public function getIsFree() {
		
	}

    public function getIsPaid() {
		
	}
	
    public function getDueAmount() {
		return 10;
	}
	
	
	public function getDiscountAmount() {
		return 0;
	}
    
    public function getAbsorbedServiceCharges() {
		return 0;
	}
    
	public function getServiceCharges() {
		return 0;
	}
	
	public function getTaxCharges() {
		return 0;
	}
	
    public function getSubtotal() {
	}
	
    public function getNetTotal() {
		return 10;
	}

    public function getBillItems() {
		return [new TestPayableItem];
	}
}

class TestPayableWithException extends TestPayable {
	protected $throwException = true;
	
	public function billTo($userId = null) {
		$invoice = Invoice::createFromBillableModel($this, $userId);
		if ($this->throwException) throw new \Exception('Error');
		$this->link('invoice', $invoice);
		return $invoice;
	}
}

class TestPayableWithException2 extends TestPayable {
	public function paymentSuccessCallBack() {
		throw new \Exception('Error');
	}
}
