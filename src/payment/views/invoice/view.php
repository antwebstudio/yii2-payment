<?php
use common\modules\payment\widgets\InvoiceSummary;

?>

<?= InvoiceSummary::widget([
    'model' => $model,
]) ?>