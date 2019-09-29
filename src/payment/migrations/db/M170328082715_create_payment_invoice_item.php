<?php

namespace ant\payment\migrations\db;

use yii\db\Migration;

class M170328082715_create_payment_invoice_item extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
		
		$this->createTable('{{%payment_invoice_item}}', [
            'id' => $this->primaryKey()->unsigned(),
			'invoice_id' => $this->integer()->notNull()->unsigned(),
			'item_content_id' => $this->integer()->notNull()->defaultValue(0),
			'title' => $this->string()->notNull(),
			'description' => $this->text(),
			'unit' => $this->smallInteger()->notNull()->defaultValue(1),
			'amount' => $this->money(12, 2)->notNull(),
			'remark' => $this->string(),
            'created_at' => $this->timestamp()->defaultValue(NULL),
            'updated_at' => $this->timestamp()->defaultValue(NULL),
		], $tableOptions);
		
		$this->addForeignKey('payment_invoice_item_invoice_id_fk', '{{%payment_invoice_item}}', 'invoice_id', '{{%payment_invoice}}', 'id', 'CASCADE', 'RESTRICT');
    }

    public function down()
    {
		$this->dropTable('{{%payment_invoice_item}}');;
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
