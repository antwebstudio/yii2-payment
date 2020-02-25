<?php
/*use ant\payment\models\Payment;
use ant\payment\models\Invoice;
use ant\payment\models\InvoiceItem;
*/
use ant\payment\components\PaymentMethod;
use ant\payment\interfaces\Payable;
use ant\payment\interfaces\Billable;
use ant\payment\interfaces\PayableItem;
use ant\payment\interfaces\BillableItem;

class CustomPaymentMethodCest
{
    public function _before(UnitTester $I)
    {
		\Yii::configure(\Yii::$app, [
            'components' => [
				'payment' => [
					'class' => 'ant\payment\components\PaymentComponent',
					'paymentGateway' => [
						'custom' => [
							'class' => 'CustomPaymentMethod',
						],
					],
				],
			],
		]);
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function test(UnitTester $I)
    {
		$payable = $this->getPayable();
		$paymentMethod = Yii::$app->payment->getPaymentMethod('custom');
		$record = $paymentMethod->pay($payable);
		
		$record->refresh();
		
		$I->assertTrue($record->id > 0);
		$I->assertEquals(100, $record->amount);
		$I->assertEquals(1, $record->is_valid);
		$I->assertEquals('MYR', $record->currency);
		$I->assertEquals(get_class($paymentMethod), $record->payment_gateway);
		$I->assertTrue(isset($record->transaction_id));
		/*
		throw new \Exception($I->renderDbTable('{{%payment}}', [
			'payment_gateway', 'transaction_id', 'amount', 'status', 'is_valid', 'data'
			//'invoice_id', 'ref_no', 'currency', 'signature', 'merchant_code', 'error', 'remark', 'paid_by', 'paid_at',
		]));
		*/
		
    }
	
	protected function getPayable() {
		$payable = new TestPayable2;
		if (!$payable->save()) throw new \Exception(print_r($payable->errors, 1));
		return $payable;
	}
}

class CustomPaymentMethod extends PaymentMethod {
	public function initGateway() {
        //$this->_gateway = \Omnipay\Omnipay::create('IPay88');
	}
	
	public function getPaymentRecordData() {
		return [
			'amount' => 100,
			'currency' => 'MYR',
			'is_valid' => 1,
			'transaction_id' => uniqid(),
		];
    }
	
	public function getPurchaseRequestAmountParamName() {
		return 'amount';
    }
}

class TestPayable2 extends \yii\db\ActiveRecord implements Payable, Billable {
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
		return [new TestPayable2Item];
	}

	public function getPaidAmount() {
		return 0;
	}
}