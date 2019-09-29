<?php

namespace ant\payment\migrations\db;

use common\components\Migration;

/**
 * Class M190815062056AlterPaymentInvoice
 */
class M190815062056AlterPaymentInvoice extends Migration
{
    protected $tableName = '{{%payment_invoice}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'billed_to', $this->integer()->unsigned()->null()->defaultValue(null));
        $this->addForeignKeyTo('{{%contact}}', 'billed_to');
        
        $this->alterColumn($this->tableName, 'issue_to', $this->integer()->null()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKeyTo('{{%contact}}', 'billed_to');
        $this->dropColumn($this->tableName, 'billed_to');

        $this->alterColumn($this->tableName, 'issue_to', $this->integer()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M190815062056AlterPaymentInvoice cannot be reverted.\n";

        return false;
    }
    */
}
