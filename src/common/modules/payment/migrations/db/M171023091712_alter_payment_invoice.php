<?php

namespace common\modules\payment\migrations\db;

use yii\db\Migration;

class M171023091712_alter_payment_invoice extends Migration
{
    public $tableName = '{{%payment_invoice}}';
	
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'tax_amount', $this->money(12, 2)->notNull()->defaultValue(0)->after('total_amount'));
		$this->addColumn($this->tableName, 'service_charges_amount', $this->money(12, 2)->notNull()->defaultValue(0)->after('total_amount'));
		$this->addColumn($this->tableName, 'discount_amount', $this->money(12, 2)->notNull()->defaultValue(0)->after('total_amount'));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'tax_amount');
        $this->dropColumn($this->tableName, 'service_charges_amount');
		$this->dropColumn($this->tableName, 'discount_amount');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M171023091712_alter_payment_invoice cannot be reverted.\n";

        return false;
    }
    */
}
