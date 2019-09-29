<?php

namespace ant\payment\migrations\db;

use yii\db\Migration;

class M181015040836_alter_payment_invoice extends Migration
{
    protected $tableName = '{{%payment_invoice}}';

    public function safeUp()
    {
		$this->addColumn($this->tableName, 'absorbed_service_charges', $this->money(12, 2)->notNull()->defaultValue(0)->after('service_charges_amount'));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'absorbed_service_charges');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M181015040836_alter_payment_invoice cannot be reverted.\n";

        return false;
    }
    */
}
