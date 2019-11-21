<?php

namespace ant\payment\migrations\db;

use yii\db\Migration;

/**
 * Class M191120115241AlterPayment
 */
class M191120115241AlterPayment extends Migration
{
    protected $tableName = '{{%payment}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->alterColumn($this->tableName, 'invoice_id',  $this->integer()->null()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->alterColumn($this->tableName, 'invoice_id',  $this->integer()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M191120115241AlterPayment cannot be reverted.\n";

        return false;
    }
    */
}
