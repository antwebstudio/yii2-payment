<?php

namespace ant\payment\migrations\db;

use yii\db\Migration;

class M180626065332_alter_payment extends Migration
{
    protected $tableName = '{{%payment}}';

    public function safeUp()
    {
		$this->addColumn($this->tableName, 'paid_at',  $this->timestamp()->defaultValue(null)->after('paid_by'));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'paid_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180626065332_alter_payment cannot be reverted.\n";

        return false;
    }
    */
}
