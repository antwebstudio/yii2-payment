<?php

namespace ant\payment\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use ant\behaviors\TimestampBehavior;
use ant\payment\models\Invoice;

/**
 * This is the model class for table "{{%payment}}".
 *
 * @property string $id
 * @property string $payment_gateway
 * @property string $transaction_id
 * @property string $amount
 * @property integer $invoice_id
 * @property integer $ref_no
 * @property string $currency
 * @property integer $status
 * @property integer $is_valid
 * @property string $signature
 * @property string $merchant_code
 * @property string $error
 * @property string $remark
 * @property integer $paid_by
 * @property string $data
 * @property string $created_at
 * @property string $updated_at
 */
class Payment extends \yii\db\ActiveRecord
{
    public $attachments;
    
    const STATUS_SUCCESS = 0;
    const STATUS_PENDING = 1;
    const STATUS_INVALID = 2;

    const EVENT_APPROVED = 'event_approved';
    const EVENT_UNAPPROVED = 'event_unapproved';
	
	public function init() {
	}

	public function behaviors() {
		$behaviors = [
			BlameableBehavior::className() => [
				'class' => BlameableBehavior::className(),
				'createdByAttribute' => 'paid_by',
				'updatedByAttribute' => null,
			],
			[
				'class' => TimestampBehavior::className(),
			],
			[
				'class' => \ant\behaviors\SerializeBehavior::className(),
				'serializeMethod' => \ant\behaviors\SerializeBehavior::METHOD_JSON,
				'attributes' => ['data'],
			],
            [
                'class' => \ant\behaviors\AttachBehaviorBehavior::className(),
                'config' => '@common/config/behaviors.php',
            ],
		];
		
		if (Yii::$app->getModule('file') != null) {
			$behaviors[] = [
				'class' => \ant\file\behaviors\AttachmentBehavior::className(),
				'attribute' => 'attachments',
				'modelType' => \ant\payment\models\Payment::className(),
				'multiple' => true,
			];
		}
		return $behaviors;
	}
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_gateway', 'amount', 'invoice_id', 'currency', 'data', 'status'], 'required'],
            [['amount'], 'number'],
            [['invoice_id', 'status', 'is_valid', 'paid_by'], 'integer'],
            [['attachments', 'paid_at', 'created_at', 'updated_at'], 'safe'],
            [['payment_gateway', 'transaction_id', 'signature', 'error', 'remark'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 10],
            [['ref_no'], 'string', 'max' => 30],
            [['merchant_code'], 'string', 'max' => 100],
            [['payment_gateway', 'transaction_id'], 'unique', 'targetAttribute' => ['payment_gateway', 'transaction_id'], 'message' => 'The combination of Payment Gateway and Transaction ID has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_gateway' => 'Payment Gateway',
            'transaction_id' => 'Transaction ID',
            'amount' => 'Amount',
            'invoice_id' => 'Invoice ID',
            'ref_no' => 'Ref No',
            'currency' => 'Currency',
            'status' => 'Status',
            'is_valid' => 'Is Valid',
            'signature' => 'Signature',
            'merchant_code' => 'Merchant Code',
            'error' => 'Error',
            'remark' => 'Remark',
            'paid_by' => 'Paid By',
            'data' => 'Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function approve() {
        $this->status = self::STATUS_SUCCESS;
        $this->is_valid = 1;
        return $this;
    }

    public function unapprove() {
        $this->status = self::STATUS_PENDING;
        $this->is_valid = 0;
        return $this;
    }

    public function beforeValidate() {
        $this->amount = str_replace([',', ' '], '', $this->amount);
        return parent::beforeValidate();
    }

    public function getIsManual() {
        // @TODO remove hard code
        if ($this->payment_gateway == 'ant\payment\components\BankWirePaymentMethod') {
            return true;
        }
        return false;
    }
	
	public function getIsValid() {
		return $this->is_valid;
	}
    public function getInvoice()
    {
        return $this->hasOne(Invoice::className(), ['id' => 'invoice_id']);
    }

    public static function findByTransactionId($transactionId) {
        return self::findOne(['transaction_id' => $transactionId]);
    }
}
