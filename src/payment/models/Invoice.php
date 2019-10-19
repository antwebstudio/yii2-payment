<?php
namespace ant\payment\models;

use Yii;
use yii\helpers\Html;
use yii\db\ActiveRecord;

use ant\helpers\Currency;
use ant\payment\models\Payable;
use ant\payment\interfaces\BillableItem;
use common\modules\order\models\Order;
use ant\user\models\User;
use common\modules\contact\models\Contact;
use ant\organization\models\Organization;

/**
 * This is the model class for table "{{%payment_invoice}}".
 *
 * @property string $id
 * @property string $formatted_id
 * @property string $total_amount
 * @property string $paid_amount
 * @property integer $issue_to
 * @property integer $issue_by
 * @property string $due_date
 * @property string $issue_date
 * @property integer $status
 * @property string $remark
 * @property string $created_at
 * @property string $updated_at
 *
 * @property PaymentInvoiceItem[] $paymentInvoiceItems
 */
class Invoice extends ActiveRecord implements Payable
{
	const STATUS_UNPAID = 0;
	const STATUS_ACTIVE = 0;
	const STATUS_PAID = 1;
	const STATUS_PAID_MANUALLY = 2;

	const STATUS_TEXT_DEFAULT = 'Unknown';

	const EVENT_PAID = 'paid';

	protected $_calculatedTotalAmount;
	protected $_calculatedPaidAmount;
	protected $_items = [];
	protected $_billedTo;

	public function behaviors()
	{
		return
		[
            [
                'class' => \common\behaviors\AttachBehaviorBehavior::className(),
                'config' => '@common/config/behaviors.php',
            ],
			[
				'class' => \common\behaviors\TimestampBehavior::className(),
			],
			'timestamp' => [
				'class' => \common\behaviors\DateTimeAttributeBehavior::className(),
				'attributes' => ['issue_date'],
			],
			'formattedAutoColumn' => [
				'class' => \common\behaviors\FormattedAutoIncreaseColumnBehavior::className(),
				'format' => isset(Yii::$app->getModule('payment')->invoiceNumberFormat) ? Yii::$app->getModule('payment')->invoiceNumberFormat : '#{id:5}',
				'saveToAttribute' => 'formatted_id',
				'createdDateAttribute' => 'issue_date',
			],
			[
				'class' => \common\behaviors\DuplicatableBehavior::className(),	
			],
			[
                'class' => 'common\behaviors\DateTimeAttributeBehavior',
                'attributes' => [
					'created_at', 'updated_at',
				],
            ],
			'privateUrl' => [
				'class' => \common\behaviors\PrivateUrlBehavior::class,
				'modelClassId' => \common\models\ModelClass::getClassId(self::class),
				'route' => '/payment/invoice/view-by-link',
				'uniqueSlug' => true,
				'autoSlug' => function($owner) {
					$hashid = new \Hashids\Hashids('InvoiceHashId', 4, 'abcdefghijklmnopqrstuvwxyz');
					return $hashid->encode($owner->id);
				}
			],
		];
	}

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payment_invoice}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return
		[
			//[['total_amount'/*, 'issue_to'*/], 'required'],
			[['total_amount'], 'default', 'value' => 0],
            [['total_amount', 'paid_amount'], 'number'],
            [['billed_to', 'issue_to', 'issue_by', 'status'], 'integer'],
            [['due_date', 'issue_date', 'created_at', 'updated_at'], 'safe'],
            [['formatted_id'], 'string', 'max' => 50],
            [['remark'], 'string', 'max' => 255],
            [['formatted_id'], 'unique'],
        ];
	}

    public function fields()
    {
        return \yii\helpers\ArrayHelper::merge(parent::fields(), [
			'subtotal',
			'netTotal',
		]);
    }

	public static function encodeId($id) {
		$hashid = new \Hashids\Hashids('InvoiceHashId', 4, 'abcdefghijklmnopqrstuvwxyz');
		$hash = $hashid->encode($id);
		return $hash;
	}

	public static function decodeId($hash) {
		$hashid = new \Hashids\Hashids('InvoiceHashId', 4, 'abcdefghijklmnopqrstuvwxyz');
		list($id) = $hashid->decode($hash);
		return $id;
	}
	
	public function duplicateWithInvoiceItems($cartItems) {
		$transaction = Yii::$app->db->beginTransaction();
		$this->formatted_id = null;
		$newInvoice = $this->duplicate();
		foreach ($this->paymentInvoiceItems as $index => $item) {
			$item = $item->duplicate();
			$item->invoice_id = $newInvoice->id;
			if(isset($cartItems[$index])){
				$item->item_id = $cartItems[$index]->id;
			} else {
				throw new \Exception("Unexpected process, invoice item not match cart item", 1);
			}
			if (!$item->save()) throw new \Exception('Failed to duplicate invoice item. ');
		}

		$transaction->commit();
		return $newInvoice;
	}

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return
		[
            'id' 			=> 'ID',
            'formatted_id' 	=> 'Formatted ID',
            'total_amount' 	=> 'Total Amount',
            'paid_amount' 	=> 'Paid Amount',
            'issue_to' 		=> 'Issue To',
            'issue_by' 		=> 'Issue By',
            'due_date' 		=> 'Due Date',
            'issue_date' 	=> 'Issue Date',
            'status' 		=> 'Status',
            'remark' 		=> 'Remark',
            'created_at' 	=> 'Created At',
            'updated_at' 	=> 'Updated At',
        ];
    }
	
	public function getAdminPanelPrivateRoute($params = []) {
		return $this->getPrivateRoute($params);
	}
	
	public function refund($amount) {
		return $this->_pay( -$amount);
	}
	
	public function refundManually($amount) {
		return $this->_pay( -$amount, self::STATUS_PAID_MANUALLY);
	}
	
	public function payManually($amount, $save = true) {
		return $this->_pay($amount, self::STATUS_PAID_MANUALLY, $save);
	}
	
	public function pay($amount, $status = Invoice::STATUS_PAID, $save = true) {
		return $this->_pay($amount, $status, $save);
	}

	protected function _pay($amount, $status = null, $save = true)
	{
		//if ($this->dueAmount <= $amount)
		//{
			$this->paid_amount += $amount;

			if (isset($status)) {
				$this->status = $status;
			} else if ($this->dueAmount > 0) {
				$this->status = self::STATUS_UNPAID;
			}

			$this->_calculatedPaidAmount = null;

			if ($this->paid_amount == $this->total_amount) {
				$this->trigger(self::EVENT_PAID);
			}
		//}
		if ($save) {
			if (!$this->save()) throw new \Exception('Payment failed. ');
		}
		return true;
	}
	
	public function refreshNetTotal($save = true) {
		$this->total_amount = $this->getCalculatedTotalAmount();
		if ($save) $this->save();
	}

	public function getCurrency()
	{
		return 'MYR';
	}

	public function getRoute() {
		return ['/payment/invoice/view', 'id' => $this->id];
	}
	
	public function getPayRoute($paymentMethod, $cancelUrl = null) {
		return [
			'/payment/default/pay', 
			'payMethod' => $paymentMethod, 
			'payId' => $this->id, 
			'cancelUrl' => isset($cancelUrl) ? Url::to($cancelUrl) : null, 
			'type' => 'invoice'
		];
	}

	public function getReference()
	{
		if (strlen($this->formatted_id)) {
			return $this->formatted_id;
		} else if (isset($this->id)) {
			$this->formatted_id = $this->generateFormattedId();
			if ($this->formatted_id != $this->id) {
				$this->save();
			}
			return $this->formatted_id;
		}
	}

	public function getCalculatedPaidAmount() {
		if (!isset($this->_calculatedPaidAmount)) {
			$paidAmount = 0;
			$payments = $this->getPayments()->all();
			
			if (isset($payments)) {
				foreach ($payments as $payment) {
					if ($payment->isValid) {
						$paidAmount += $payment->amount;
					}
				}
			}
			$this->_calculatedPaidAmount = $paidAmount;
		}
		if ($this->status != self::STATUS_PAID_MANUALLY && (double) $this->_calculatedPaidAmount != (double) $this->paid_amount) throw new \Exception('Paid amount recorded in database is not correct. (Invoice ID: '.$this->id.', recorded: '.$this->paid_amount.', calculated: '.$this->_calculatedPaidAmount.')');

		return Currency::rounding($this->_calculatedPaidAmount);
	}

	public function getCalculatedTotalAmount() {
		return Currency::rounding($this->getCalculatedSubtotal() + $this->getCalculatedAmountNotIncludedInSubtotal() - $this->getDiscountAmount() + $this->getServiceCharges() + $this->getTaxCharges());
	}                                                                                    
	
	public function getNetTotal() {
		return Currency::rounding($this->getCalculatedTotalAmount());
	}

	public function getSubtotal() {
		return $this->getCalculatedSubtotal();
	}
	
	public function getTaxCharges() {
		return $this->tax_amount;
	}
	
	public function getServiceCharges() {
		return $this->service_charges_amount;
	}
	
	protected function getCalculatedAmountNotIncludedInSubtotal() {
		$amount = 0;

		foreach ($this->billItems as $item) {
			if (!$item->included_in_subtotal) {
				$amount += $item->netTotal;
			}
		}
		return $amount;
	}

	public function getCalculatedSubtotal() {
		if (!isset($this->_calculatedTotalAmount)) {
			$amount = 0;

			foreach ($this->billItems as $item) {
				if ($item->included_in_subtotal) {
					$amount += $item->netTotal;
				}
			}
			$this->_calculatedTotalAmount = $amount;
		}
		return Currency::rounding($this->_calculatedTotalAmount);
	}

	public function getIsFree()
	{
		return $this->total_amount == 0;
	}

	public function getIsPaid()
	{
		return $this->getIsFree() ? $this->status == self::STATUS_PAID : $this->getDueAmount() <= 0;
	}
	
	public function getDiscountAmount() {
		return $this->discount_amount;
	}
	
	public function getPaidAmount() {
		if ($this->status == self::STATUS_PAID) {
			return $this->getCalculatedPaidAmount();
		} else if ($this->status == self::STATUS_PAID_MANUALLY) {
			return Currency::rounding($this->paid_amount);
		}
		return Currency::rounding($this->paid_amount);
	}

	public function getDueAmount() {
		if ($this->getCalculatedTotalAmount() != $this->total_amount) {
			throw new \Exception('Calculated total amount is not equal to total amount recorded. (Invoice ID: '.$this->id.', calculated: '.$this->getCalculatedTotalAmount().', recorded: '.$this->total_amount.')');
		}
		return Currency::rounding($this->getCalculatedTotalAmount() - $this->getPaidAmount());
	}
	
	public function getBillItems() {
		return $this->getPaymentInvoiceItems();
	}
	
	public function getPaymentItems() {
		return $this->getPaymentInvoiceItems();
	}

	public function getInvoiceItems() {
		return $this->hasMany(InvoiceItem::className(), ['invoice_id' => 'id']);
	}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentInvoiceItems()
    {
        return $this->getInvoiceItems();
    }

	public function getPayments()
    {
        return $this->hasMany(Payment::className(), ['invoice_id' => 'id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['invoice_id' => 'id']);
	}
	
	public function getIssuedTo() {
		if (YII_DEBUG) throw new \Exception('DEPRECATED');

		return $this->hasOne(\common\modules\user\models\User::className(), ['id' => 'issue_to']);
	}
	
	public function getBilledTo() {
		if (isset($this->issue_to) && $this->issue_to) {
			return $this->hasOne(\common\modules\contact\models\Contact::className(), ['id' => 'billed_to']);
		} else if (isset($this->order)) {
			if (!YII_DEBUG) return $this->order->billedTo;				
		}
		return $this->hasOne(\common\modules\contact\models\Contact::className(), ['id' => 'billed_to']);
	}

	public function getContact(){
		return $this->hasOne(\common\modules\contact\models\Contact::className(), ['id' => 'issue_to']);
	}
	
	public static function find() {
		return new \ant\payment\models\query\InvoiceQuery(get_called_class());
	}

	public static function createFromBillableItem(BillableItem $billableItem, $billTo = null) {
		$userId = $billTo instanceof User ? $billTo->id : null;
		$contactId = $billTo instanceof Contact ? $billTo->id : null;
		$contactId = $billTo instanceof Organization ? $billTo->contact_id : $contactId;
		$contactId = $billTo instanceof User ? $billTo->profile->contact_id : $contactId;
		$organizationId = $billTo instanceof Organization ? $billTo->id : null;

		$invoice = new Invoice([
			'total_amount' => $billableItem->getUnitPrice(),
			'discount_amount' => 0,
			'service_charges_amount' => 0,
			'tax_amount' => 0,
			'paid_amount' => 0,
			'issue_to' => $userId,
			'billed_to' => $contactId,
			'organization_id' => $organizationId,
			'remark' => 'not set',
			'status' => Invoice::STATUS_ACTIVE,
		]);
		$invoice->save();
		
		$invoiceItem = InvoiceItem::createFromBillableItem($billableItem);
		$invoiceItem->link('invoice', $invoice);
		$invoiceItem->save();
		
		return $invoice;
	}
	
	public static function createFromBillableModel($payableModel, $billTo = null) {
		if (YII_DEBUG) {
			if (isset($billTo)) {
				if (!($billTo instanceof User) && !($billTo instanceof Contact)) {
					throw new \Exception('Second parameter should be either instance of User or instance of Contact.');
				}
			}
		}

		if ($billTo instanceof User) {
			$userId = $billTo->id;
			$contactId = isset($billTo->profile->contact) ? $billTo->profile->contact->id : null;
		} else if ($billTo instanceof Contact) {
			$userId = isset($billTo->user) ? $billTo->user->id : null;
			$contactId = $billTo->id;
		} else {
			$userId = $billTo;
			$contactId = null;
		}

		$transaction = Yii::$app->db->beginTransaction();
		// Create invoice
		$invoice = new Invoice([
			'total_amount' => $payableModel->getNetTotal(),
			'discount_amount' => $payableModel->getDiscountAmount(),
			'absorbed_service_charges' => $payableModel->getAbsorbedServiceCharges(),
			'service_charges_amount' => $payableModel->getServiceCharges(),
			'tax_amount' => $payableModel->getTaxCharges(),
			'status' => Invoice::STATUS_ACTIVE,
			'billed_to' => $contactId,
		]);

		if (isset($userId)) {
			$invoice->issue_to = $userId;
		} else if(!Yii::$app->user->isGuest) {
			if (YII_DEBUG) throw new \Exception('DEPRECATED');
			$invoice->issue_to = Yii::$app->user->identity->id;
		} else {
			//if (YII_DEBUG) throw new \Exception('DEPRECATED');
			//$invoice->issue_to = 0;
		}

		if(!$invoice->save())
		{
			$transaction->rollBack();
			throw new \Exception(Html::errorSummary($invoice));
			return false;
		}

		// Create invoice items
		foreach ($payableModel->billItems as $item)
		{
			if(!($item instanceof BillableItem)) {
				throw new \Exception('Invalid Billable Item. ('.get_class($item).')');
				return false;
			}

			$invoiceItem = new InvoiceItem();
			$invoiceItem->item_id = $item->id;
			$invoiceItem->title = $item->title;
			$invoiceItem->quantity = $item->quantity;
			$invoiceItem->unit_price = $item->unitPrice;
			$invoiceItem->description = $item->description;
			$invoiceItem->included_in_subtotal = $item->getIncludedInSubtotal() ? 1 : 0;
			$invoiceItem->setDiscount($item->getDiscount());
			
			$invoiceItem->invoice_id = $invoice->id;

			if(!$invoiceItem->save())
			{
				$transaction->rollBack();
				throw new \Exception(Html::errorSummary($invoiceItem));
				return false;
			}
		}
		
		// Validate invoice
		if ($invoice->getCalculatedTotalAmount() != $invoice->total_amount) {
			$transaction->rollBack();
			throw new \Exception('Calculated total amount is not equal to total amount recorded. (Invoice ID: '.$invoice->id.', calculated: '.$invoice->getCalculatedTotalAmount().', recorded: '.$invoice->total_amount.')');
		}

		$transaction->commit();

		$payableModel->trigger(Billable::EVENT_AFTER_BILL_SUCCESS, new \yii\base\Event(['sender' => $invoice]));
		
		return $invoice;
	}

	public static function statusOptions() {
		return [
			self::STATUS_ACTIVE => [
				'label' => 'Active',
				'cssClass' => 'badge-warning badge label-warning',
			],
			self::STATUS_PAID => [
				'label' => 'Paid',
				'cssClass' => 'badge-success badge label-success',
			],
		];
	}
	
	public function getStatusHtml() {
		$options = self::statusOptions();
		
		if (isset($options[$this->status])) {
			return '<span class="'.$options[$this->status]['cssClass'].'">'.$options[$this->status]['label'].'</span>';
		} else {
			return '<span class="badge badge-light">Unknown</span>';
		}
	}

	public function getStatusOption($option = null, $default = null) {
		$status = $this->status;
		$options = self::statusOptions();
		if (isset($option)) {
			if ($this->status == self::STATUS_PAID_MANUALLY ) {
				$status = self::STATUS_PAID;
			}
			if (isset($option)) {
				$value = isset($options[$status][$option]) ? $options[$status][$option] : null;
			} else {
				$value = isset($options[$status]) ? $options[$status] : null;
			}
			
			if (!isset($value)) {
				return $default;
			}
			return $value;
		} else {
			return $options;
		}
	}
}
