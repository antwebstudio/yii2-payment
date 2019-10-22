<?php

namespace ant\discount\migrations\db;

use yii\db\Migration;

class M170928080451_create_discount_rule_condition extends Migration
{
	public $tableName = '{{%discount_rule_condition}}';
	
    public function safeUp()
    {
		$tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->unsigned(),
			'discount_rule_id' => $this->integer()->unsigned()->notNull(),
            'product_id' => $this->integer(11)->defaultValue(NULL),
            'category_id' => $this->integer(11)->defaultValue(NULL),
            'user_id' => $this->integer(11)->defaultValue(NULL),	
			'status' => $this->smallInteger(3)->notNull()->defaultValue(0),
            //'created_by' => $this->integer(11)->unsigned(),
            //'updated_by' => $this->integer(11)->unsigned(),
            //'created_at' => $this->timestamp()->defaultValue(NULL),
            //'updated_at' => $this->timestamp()->defaultValue(NULL),
        ], $tableOptions);
		
        $this->addForeignKey('fk_discount_rule_conditions_discount_rule_id', $this->tableName, 'discount_rule_id', '{{%discount_rule}}', 'id', 'restrict', 'cascade');
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
        echo "M170928080451_create_discount_rule_conditions cannot be reverted.\n";

        return false;
    }
    */
}
