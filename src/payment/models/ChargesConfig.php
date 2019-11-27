<?php

namespace ant\payment\models;

use Yii;

/**
 * This is the model class for table "em_payment_charges_config".
 *
 * @property integer $id
 * @property string $label
 * @property integer $type
 * @property integer $percentage
 * @property integer $amount
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Event[] $events
 */
class ChargesConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payment_charges_config}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'percentage', 'amount', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['label'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'label' => 'Label',
            'type' => 'Type',
            'percentage' => 'Percentage',
            'amount' => 'Amount',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEvents()
    {
        return $this->hasMany(Event::className(), ['service_fee' => 'id']);
    }

    public function getFullLabel() {
        $percentage = $this->percentage ? $this->percentage.'% ' : '';
        $amount = $this->amount ? 'RM '.$this->amount : '';
        $plus = $percentage && $amount ? ' + ' : '';
        
        return $this->label.' - '.$percentage.$plus.$amount;
    }
}
