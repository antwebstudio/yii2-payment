<?php

namespace ant\payment\migrations\db;

use yii\db\Migration;

class M170328082710_create_payment_invoice extends Migration
{
    public function up()
    {
		$tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

		$this->createTable('{{%payment_invoice}}', [
            'id' => $this->primaryKey()->unsigned(),
			'formatted_id' => $this->string(50),
			'total_amount' => $this->money(12, 2)->notNull(),
			'paid_amount' => $this->money(12, 2)->notNull()->defaultValue(0),
			'issue_to' => $this->integer()->notNull(),
			'issue_by' => $this->integer(),
			'due_date' => $this->timestamp()->defaultValue(NULL),
			'issue_date' => $this->timestamp()->defaultValue(NULL),
			'status' => $this->smallInteger()->notNull()->defaultValue(0),
			'remark' => $this->string()->notNull()->defaultValue(''),
            'created_at' => $this->timestamp()->defaultValue(NULL),
            'updated_at' => $this->timestamp()->defaultValue(NULL),
		], $tableOptions);

		$this->createIndex('payment_invoice_formatted_id_unq_idx', '{{%payment_invoice}}', 'formatted_id', true);
    }

    public function down()
    {
        $this->dropTable('{{%payment_invoice}}');
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
