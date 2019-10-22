<?php

namespace ant\discount\models;

use Yii;

/**
 * This is the model class for table "em_discount_rule".
 *
 * @property integer $id
 * @property string $class
 * @property integer $priority
 * @property string $discount_amount
 * @property string $discount_percent
 * @property string $code
 * @property integer $status
 * @property string $product_ids
 * @property string $category_ids
 * @property string $user_ids
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class DiscountRule extends \yii\db\ActiveRecord
{

	/**
     * @inheritdoc
     */
	public function behaviors()
	{
		return 
        [
			[
				'class' => \ant\behaviors\TimestampBehavior::className(),
			],
			[
				'class' => \yii\behaviors\BlameableBehavior::className(),
			],
            [
                'class' => \ant\behaviors\SerializeBehavior::className(),
				'serializeMethod' => \ant\behaviors\SerializeBehavior::METHOD_JSON,
                'attributes' => ['user_ids', 'product_ids', 'category_ids']
            ],
		];
	}
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%discount_rule}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class', 'priority'], 'required'],
            [['priority', 'status', 'created_by', 'updated_by'], 'integer'],
            [['discount_amount', 'discount_percent'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['class'], 'string', 'max' => 512],
            [['code'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'class' => 'Class',
            'priority' => 'Priority',
            'discount_amount' => 'Discount Amount',
            'discount_percent' => 'Discount Percent',
            'code' => 'Code',
            'status' => 'Status',
            'product_ids' => 'Product Ids',
            'category_ids' => 'Category Ids',
            'user_ids' => 'User Ids',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
