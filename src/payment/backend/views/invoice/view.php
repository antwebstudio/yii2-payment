<?php 
use yii\helpers\Html;
use yii\helpers\Url;
use ant\user\models\User;
use ant\rbac\Permission;
use ant\simpleCommerce\helpers\WhatsApp;

$this->context->layout = '//base';
$url = Yii::$app->frontendUrlManager->createAbsoluteUrl($model->privateRoute);
$whatsAppText = 'Thank you for purchase with us. '."\n\n".'Please check your invoice at: '.$url;
$whatsAppText = Yii::t('payment', $whatsAppText);
?>
<?= \ant\widgets\Alert::widget() ?>

<?php if (Permission::can('pay', \ant\payment\backend\controllers\InvoiceController::class)): ?>
	<?php $form = \yii\widgets\ActiveForm::begin(['action' => ['/payment/backend/invoice/pay', 'id' => $model->id]]) ?>
		<div class="form-group">
			<?= Html::hiddenInput('transactionId', 'F2F_'.uniqid()) ?>
			<?= Html::textInput('amount', $model->dueAmount, ['number' => true, 'class' => 'form-control']) ?>
			
			<?= Html::submitButton('Pay', ['class' => 'btn-primary']) ?>
		</div>
	<?php \yii\widgets\ActiveForm::end() ?>
<?php endif ?>

<div class="row">
	<div class="offset-md-2 col-md-8">
		<div class="card">
			<div class="card-body text-center">
				<a class="btn btn-dark" href="<?= Url::to(['/order/backend', 'view' => 'simplecommerce']) ?>"><?= Yii::t('order', 'Back') ?></a>
				<a class="btn btn-secondary" target="_blank" href="<?= WhatsApp::apiUrl(WhatsApp::addCountryPrefix($model->billedTo->contact_number), $whatsAppText) ?>">WhatsApp</a>
				<a class="btn btn-primary" target="_blank" href="<?= Yii::$app->frontendUrlManager->createUrl($model->privateRoute) ?>">Preview</a>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="offset-md-2 col-md-8">
		<?= \ant\payment\widgets\BillableView::widget([
				'model' => $model
			]) 
		?>
	</div>
</div>

<?php if (Permission::can('view-payment-record', \ant\payment\models\Invoice::class)): ?>
<div class="row">
	<div class="col-md-12">
		<div class='table-responsive'>
		<?= \ant\payment\widgets\PaymentSummary::widget([
				'invoiceId' => $model->id
			]) 
		?>
		</div>
	</div>
</div>
<?php endif ?>

<?php if (YII_DEBUG): ?>
	<h2>Debug</h2>
	<?= \yii\widgets\DetailView::widget(['model' => $model]) ?>
<?php endif ?>