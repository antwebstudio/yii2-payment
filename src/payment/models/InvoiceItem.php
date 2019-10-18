<?php

namespace ant\payment\models;

use Yii;
use common\helpers\Currency;
use common\modules\discount\helpers\Discount;
use common\modules\payment\models\PayableItem;

/**
 * This is the model class for table "{{%payment_invoice_item}}".
 *
 * @property string $id
 * @property string $invoice_id
 * @property integer $item_id
 * @property string $title
 * @property string $description
 * @property integer $quantity
 * @property string $unit_price
 * @property string $remark
 * @property string $created_at
 * @property string $updated_at
 *
 * @property PaymentInvoice $invoice
 */
class InvoiceItem extends \yii\db\ActiveRecord implements PayableItem
{
	use \common\modules\payment\traits\BillableTrait;
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payment_invoice_item}}';
    }

    public static function createFromBillableItem($billableItem, $quantity = 1) {
        return new InvoiceItem([
			'item_id' => $billableItem->id,
			'title' => $billableItem->title,
			'quantity' => $quantity,
			'unit_price' => $billableItem->unitPrice,
		]);
    }

    public function behaviors()
	{
		return
		[
			[
				'class' => \common\behaviors\DuplicatableBehavior::className(),	
			],
		];
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['invoice_id', 'title', 'unit_price'], 'required'],
            [['invoice_id', 'item_id', 'quantity'], 'integer'],
            [['unit_price'], 'number', 'min' => 0],
            [['created_at', 'updated_at', 'discount_value', 'discount_type'], 'safe'],
            [['title', 'description', 'remark'], 'string'],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::className(), 'targetAttribute' => ['invoice_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'invoice_id' => 'Invoice ID',
            'item_id' => 'Item Content ID',
            'title' => 'Title',
            'description' => 'Description',
            'quantity' => 'Unit',
            'unit_price' => 'Amount',
            'remark' => 'Remark',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::className(), ['id' => 'invoice_id']);
    }
	
	public function getNetTotal() {
		return Currency::rounding($this->getDiscountedUnitPrice() * $this->getQuantity());
	}
	
    public function getSubtotal(){
        return $this->amount * $this->quantity;
    }
	
    public function getAmount(){
        return $this->unit_price;
    }
	
    public function getQuantity(){
        return $this->quantity;
    }
	
	public function getDescription() {
		return $this->description;
	}
	
	public function getName() {
		return $this->getTitle();
	}
	
    public function getTitle(){
        return $this->title;
    }
	
    public function getId(){
        return $this->id;
    }
	
	public function getUnitPrice() {
		return $this->unit_price;
	}
	
	public function getDiscountAmount() {
		$value = $this->discount_value ? $this->discount_value : 0;
		$discount = new Discount($value, $this->discount_type);
		return $discount->of($this->unitPrice);
	}
	
	public function getDiscountedUnitPrice() {
		return Currency::rounding($this->amount - $this->discountAmount);
	}
	
	public function setDiscount($discount, $discountType = 0) {
		if ($discount instanceof \common\modules\discount\helpers\Discount) {
			$this->discount_value = $discount->value;
			$this->discount_type = $discount->type;
		} else {
			$this->discount_value = $discount;
			$this->discount_type = $discountType;
		}
	}
	
	public function getDiscount() {
		return new \common\modules\discount\helpers\Discount($this->discount_value, $this->discount_type);
	}
	
	public function deductAvailableQuantity($quantity) {
		throw new \Exception('Actually this should not be implemented. ');
	} 
}
