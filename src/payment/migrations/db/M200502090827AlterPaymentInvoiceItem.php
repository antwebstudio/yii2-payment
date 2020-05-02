<?php

namespace ant\payment\migrations\db;

use yii\db\Migration;

/**
 * Class M200502090827AlterPaymentInvoiceItem
 */
class M200502090827AlterPaymentInvoiceItem extends Migration
{
	protected $tableName = '{{%payment_invoice_item}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'additional_discount', $this->double()->defaultValue(0));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn($this->tableName, 'additional_discount');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M200502090827AlterPaymentInvoiceItem cannot be reverted.\n";

        return false;
    }
    */
}
