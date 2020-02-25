<?php 
use ant\payment\interfaces\Payable;
use ant\payment\interfaces\Billable;
use ant\payment\interfaces\PayableItem;
use ant\payment\interfaces\BillableItem;
use ant\payment\models\Invoice;

class PayableCest
{
	
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }
	
	public function _fixtures() {
		return [
			'user' => 'tests\fixtures\UserFixture',
		];
	}
	
	public function test(UnitTester $I) {
		$user = $I->grabFixture('user')->getModel(0);
		$payable = $this->getPayable();
		
		$I->assertFalse(isset($payable->invoice_id));
		
		$invoice = $payable->billTo($user);
		
		$I->assertTrue(isset($payable->invoice_id));
		$I->assertEquals($invoice->id, $payable->invoice_id);
		
		$payable->pay(10);
		$payable->refresh();
		$payable->invoice->refresh();
		
		$I->assertEquals($invoice->id, $payable->invoice_id);
		$I->assertEquals(10, $payable->invoice->paid_amount);
		
		//$payable
	}
	
	protected function getPayable() {
		$payable = new PayableCestTestPayable;
		if (!$payable->save()) throw new \Exception(print_r($payable->errors, 1));
		return $payable;
	}
}

class PayableCestTestPayableItem extends \yii\base\Component implements BillableItem {
	use \ant\payment\traits\BillableItemTrait;
	
	public function getUnitPrice() {
		return 10;
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
		return 10;
	}
}

class PayableCestTestPayable extends \yii\db\ActiveRecord implements Payable, Billable {
	use \ant\payment\traits\BillableTrait, \ant\payment\traits\PayableTrait;
	
	protected $paid = false;
	
	public static function tableName() {
		return '{{%test_payable}}';
	}
	
	public function behaviors() {
		return [
			[
				'class' => \ant\behaviors\EventHandlerBehavior::className(),
				'events' => [
					Payable::EVENT_AFTER_PAYMENT_SUCCESS => [$this, 'paymentSuccessCallBack'],
				],
			],
		];
	}
	
	public function getBill() {
		return $this->invoice;
	}

    public function getInvoice()
    {
        return $this->hasOne(Invoice::className(), ['id' => 'invoice_id']);
    }
	
	public function billTo($userId = null) {
		$invoice = Invoice::createFromBillableModel($this, $userId);
		$this->link('invoice', $invoice);
		return $invoice;
	}
	
	public function getPaidAmount() {
		
	}
	
	public function paymentSuccessCallBack() {
		$this->paid = true;
	}
	
	public function getReference() {
		return 1;
	}

    public function getCurrency() {
		return 'MYR';
	}

    public function getBillItems() {
		return [new PayableCestTestPayableItem];
	}
}