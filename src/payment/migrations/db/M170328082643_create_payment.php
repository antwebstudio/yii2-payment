<?php

namespace ant\payment\migrations\db;

use yii\db\Migration;

class M170328082643_create_payment extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
		
		$this->createTable('{{%payment}}', [
            'id' => $this->primaryKey()->unsigned(),
			'payment_gateway' => $this->string()->notNull(),
			'transaction_id' => $this->string(),
			'amount' => $this->money(12, 2)->notNull(),
			'invoice_id' => $this->integer()->notNull(),
			'ref_no' => $this->string(30),
			'currency' => $this->string(10)->notNull(),
			'status' => $this->smallInteger()->notNull()->defaultValue(0),
			'is_valid' => $this->smallInteger()->notNull()->defaultValue(0),
			'signature' =>  $this->string(),
			'merchant_code' =>  $this->string(100),
			'error' =>  $this->string(),
			'remark' =>  $this->string(),
			'paid_by' => $this->integer()->unsigned(),
			'data' => $this->text()->notNull(),
            'created_at' => $this->timestamp()->defaultValue(NULL),
            'updated_at' => $this->timestamp()->defaultValue(NULL),
		], $tableOptions);
		
		$this->createIndex('payment_payment_gateway_transaction_id_unq_idx', '{{%payment}}', 'payment_gateway, transaction_id', true);
    }

    public function down()
    {
        $this->dropTable('{{%payment}}');
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
