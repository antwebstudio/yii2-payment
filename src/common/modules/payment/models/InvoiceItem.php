<?php

namespace common\modules\payment\models;

/**
 * This is the model class for table "{{%payment_invoice_item}}".
 *
 * @property string $id
 * @property string $invoice_id
 * @property integer $item_id
 * @property string $title
 * @property string $description
 * @property integer $quantity
 * @property string $unit_price
 * @property string $remark
 * @property string $created_at
 * @property string $updated_at
 *
 * @property PaymentInvoice $invoice
 */
class InvoiceItem extends \ant\payment\models\InvoiceItem
{
	
}
