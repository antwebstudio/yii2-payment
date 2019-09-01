<?php
namespace common\modules\payment\models;

use common\modules\payment\models\Invoice;

interface PayableCallBack
{
    public function invoiceCreatedCallBack(Invoice $invoice);

    public function paymentSuccessCallBack(Invoice $invoice);
}
?>
