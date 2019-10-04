<?php
use common\modules\payment\widgets\InvoiceSummary;

?>

<?php if (YII_DEBUG): ?>
	Contact ID: <?= $model->billedTo->id ?>
<?php endif ?>

<?= InvoiceSummary::widget([
    'model' => $model,
]) ?>