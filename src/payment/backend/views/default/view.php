<?php 
use yii\helpers\Html;
use ant\user\models\User;
?>
<?php $form = \yii\widgets\ActiveForm::begin(['action' => ['/payment/invoice/pay', 'id' => $model->id]]) ?>
	<div class="form-group">
        <?= Html::hiddenInput('transactionId', 'F2F_'.uniqid()) ?>
		<?= Html::textInput('amount', $model->dueAmount, ['number' => true, 'class' => 'form-control']) ?>
		
		<?= Html::submitButton('Pay', ['class' => 'btn-primary']) ?>
	</div>
<?php \yii\widgets\ActiveForm::end() ?>

<div clas='table-responsive'>
    <?= \ant\payment\widgets\InvoiceSummary::widget([
            'model' => $model
        ]) 
    ?>

    <?= \ant\payment\widgets\PaymentSummary::widget([
            'invoice_id' => $model->id
    	]) 
    ?>
</div>