<?php

namespace ant\payment\migrations\db;

use yii\db\Migration;

class M180223034819_alter_payment_invoice_item extends Migration
{
	protected $tableName = '{{%payment_invoice_item}}';
	
    public function safeUp()
    {
		$this->renameColumn($this->tableName, 'item_content_id', 'item_id');
		$this->renameColumn($this->tableName, 'unit', 'quantity');
		$this->renameColumn($this->tableName, 'amount', 'unit_price');
		
		$this->addColumn($this->tableName, 'currency', $this->string(3)->defaultValue(NULL));
		$this->addColumn($this->tableName, 'discount_amount', $this->double()->defaultValue(0));
		//$this->addColumn($this->tableName, 'remark', $this->text()->defaultValue(NULL));
    }

    public function safeDown()
    {
		$this->renameColumn($this->tableName, 'item_id', 'item_content_id');
		$this->renameColumn($this->tableName, 'quantity', 'unit');
		$this->renameColumn($this->tableName, 'unit_price', 'amount');
		
		$this->dropColumn($this->tableName, 'currency');
		$this->dropColumn($this->tableName, 'discount_amount');
		//$this->dropColumn($this->tableName, 'remark');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180223034819_alter_payment_invoice_item cannot be reverted.\n";

        return false;
    }
    */
}
