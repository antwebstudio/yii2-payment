<?php

namespace ant\payment\migrations\db;

use yii\db\Migration;

/**
 * Class M190930140013AlterPaymentInvoice
 */
class M190930140013AlterPaymentInvoice extends Migration
{
    protected $tableName = '{{%payment_invoice}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'organization_id', $this->integer()->unsigned()->null()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn($this->tableName, 'organization_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M190930140013AlterPaymentInvoice cannot be reverted.\n";

        return false;
    }
    */
}
