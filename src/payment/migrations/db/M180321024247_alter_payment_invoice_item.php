<?php

namespace ant\payment\migrations\db;

use yii\db\Migration;

class M180321024247_alter_payment_invoice_item extends Migration
{
	protected $tableName = '{{%payment_invoice_item}}';
	
    public function safeUp()
    {
		$this->dropColumn($this->tableName, 'discount_amount');
		
		$this->addColumn($this->tableName, 'discount_value', $this->double()->defaultValue(0));
		$this->addColumn($this->tableName, 'discount_type', $this->smallInteger(1)->defaultValue(0));

    }

    public function safeDown()
    {
		$this->addColumn($this->tableName, 'discount_amount', $this->double()->defaultValue(0));
		
		$this->dropColumn($this->tableName, 'discount_value');
		$this->dropColumn($this->tableName, 'discount_type');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180321024247_alter_payment_invoice_item cannot be reverted.\n";

        return false;
    }
    */
}
