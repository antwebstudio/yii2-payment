<?php 
use yii\helpers\Html;
use common\modules\user\models\User;
?>
<?php $form = \yii\widgets\ActiveForm::begin(['action' => ['/payment/invoice/pay', 'id' => $model->id]]) ?>
	<div class="form-group">
        <?= Html::hiddenInput('transactionId', 'F2F_'.uniqid()) ?>
		<?= Html::textInput('amount', $model->dueAmount, ['number' => true, 'class' => 'form-control']) ?>
		
		<?= Html::submitButton('Pay', ['class' => 'btn-primary']) ?>
	</div>
<?php \yii\widgets\ActiveForm::end() ?>

<div clas='table-responsive'>
    <?= \common\modules\payment\widgets\InvoiceSummary::widget([
            'model' => $model
        ]) 
    ?>

    <?= \common\modules\payment\widgets\PaymentSummary::widget([
            'invoice_id' => $model->id
    	]) 
    ?>
</div>

<?php if (YII_DEBUG): ?>
	<h2>Debug</h2>
	<?= \yii\widgets\DetailView::widget(['model' => $model]) ?>
<?php endif ?>