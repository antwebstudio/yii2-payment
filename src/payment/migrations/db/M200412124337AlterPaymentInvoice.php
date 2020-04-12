<?php

namespace ant\payment\migrations\db;

use ant\db\Migration;

/**
 * Class M200412124337AlterInvoice
 */
class M200412124337AlterPaymentInvoice extends Migration
{
    protected $tableName = '{{%payment_invoice}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'billable_id', $this->morphId());
		$this->addColumn($this->tableName, 'billable_class_id', $this->morphClass());
		
		$this->addForeignKeyTo('{{%model_class}}', 'billable_class_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		//$this->dropForeignKeyTo('{{%model_class}})', 'billable_class_id');
		
		$this->dropColumn($this->tableName, 'billable_id');
		$this->dropColumn($this->tableName, 'billable_class_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M200412124337AlterInvoice cannot be reverted.\n";

        return false;
    }
    */
}
