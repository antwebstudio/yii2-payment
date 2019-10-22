<?php

namespace ant\discount\migrations\db;

use yii\db\Migration;

class M170928074239_create_discount_rule extends Migration
{
	public $tableName = '{{%discount_rule}}';
	
    public function safeUp()
    {
		$tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->unsigned(),
			'class' => $this->string(512)->notNull(),
            'priority' => $this->smallInteger(3)->notNull(),
            'discount_amount' => $this->money()->notNull()->defaultValue(0.00),
            'discount_percent' => $this->decimal(5, 2)->notNull()->defaultValue(0.00),
            'code' => $this->string(50)->defaultValue(NULL),
			'status' => $this->smallInteger(3)->notNull()->defaultValue(0),
			
            'product_ids' => $this->text()->defaultValue(NULL),
            'category_ids' => $this->text()->defaultValue(NULL),
            'user_ids' => $this->text()->defaultValue(NULL),

            'created_by' => $this->integer(11)->unsigned(),
            'updated_by' => $this->integer(11)->unsigned(),
            'created_at' => $this->timestamp()->defaultValue(NULL),
            'updated_at' => $this->timestamp()->defaultValue(NULL),
        ], $tableOptions);
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
        echo "M170928074239_create_discount_rule cannot be reverted.\n";

        return false;
    }
    */
}
