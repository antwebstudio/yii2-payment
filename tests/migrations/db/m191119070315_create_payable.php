<?php

use ant\db\Migration;
use yii\db\Expression;

class m191119070315_create_payable extends Migration
{
    public function up()
    {
        $this->createTable('{{%test_payable}}', [
            'id' => $this->primaryKey(),
			'invoice_id' => $this->integer()->null(),
			'status' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultValue(NULL),
            'updated_at' => $this->timestamp()->defaultValue(NULL),
        ], $this->getTableOptions());
    }

    public function down()
    {
       $this->dropTable('{{%test_payable}}');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
