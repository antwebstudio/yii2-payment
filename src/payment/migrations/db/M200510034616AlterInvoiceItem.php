<?php

namespace ant\payment\migrations\db;

use ant\db\Migration;
use ant\payment\models\InvoiceItem;
use ant\discount\helpers\Discount;

/**
 * Class M200510034616AlterInvoiceItem
 */
class M200510034616AlterInvoiceItem extends Migration
{
	protected $tableName = '{{%payment_invoice_item}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'discount_amount', $this->decimal(19, 4)->notNull()->defaultValue(0));
		$this->addColumn($this->tableName, 'discount_percent', $this->decimal(19, 4)->notNull()->defaultValue(0));

		InvoiceItem::updateAll(['discount_type' => Discount::TYPE_PERCENT], 'discount_percent = discount_value');
		InvoiceItem::updateAll(['discount_type' => Discount::TYPE_AMOUNT], 'discount_amount = discount_value');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn($this->tableName, 'discount_amount');
		$this->dropColumn($this->tableName, 'discount_percent');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M200510034616AlterInvoiceItem cannot be reverted.\n";

        return false;
    }
    */
}
