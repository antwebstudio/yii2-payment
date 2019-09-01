<?php

namespace common\modules\payment\migrations\db;

use common\components\Migration;

class M181007112921_create_payment_charges_config extends Migration
{
    protected $tableName = '{{%payment_charges_config}}';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->unsigned(),
            'label' => $this->string()->null()->defaultValue(null),
			'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'percentage' => $this->smallInteger()->notNull()->defaultValue(0),
            'amount' => $this->smallInteger()->notNull()->defaultValue(0),
			'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
		], $this->getTableOptions());
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M181007112921_create_payment_charges_config cannot be reverted.\n";

        return false;
    }
    */
}
