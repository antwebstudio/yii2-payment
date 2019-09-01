<?php

namespace common\modules\payment\migrations\db;

use yii\db\Migration;

/**
 * Class M190803095041_alter_payment_invoice_item
 */
class M190803095041_alter_payment_invoice_item extends Migration
{
	protected $tableName = '{{%payment_invoice_item}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn($this->tableName, 'included_in_subtotal', $this->boolean()->notNull()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn($this->tableName, 'included_in_subtotal');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M190803095041_alter_invoice_item cannot be reverted.\n";

        return false;
    }
    */
}
