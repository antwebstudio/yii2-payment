<?php

namespace ant\payment\migrations\db;

use yii\db\Migration;

/**
 * Class M191119101019AlterPaymentInvoice
 */
class M191119101019AlterPaymentInvoice extends Migration
{
    protected $tableName = '{{%payment_invoice}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'model_id', $this->integer()->unsigned()->null()->defaultValue(null));
		$this->addColumn($this->tableName, 'model_class_id', $this->integer()->unsigned()->null()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn($this->tableName, 'model_id');
		$this->dropColumn($this->tableName, 'model_class_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M191119101019AlterPaymentInvoice cannot be reverted.\n";

        return false;
    }
    */
}
