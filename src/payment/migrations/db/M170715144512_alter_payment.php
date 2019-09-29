<?php

namespace ant\payment\migrations\db;

use yii\db\Migration;

class M170715144512_alter_payment extends Migration
{
	public $tableName = '{{%payment}}';
	
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'backend_update', $this->integer(1)->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'backend_update');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M170715144512_alter_payment cannot be reverted.\n";

        return false;
    }
    */
}
