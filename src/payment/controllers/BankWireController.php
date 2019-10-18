<?php
namespace ant\payment\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\HttpException;
use yii\data\ActiveDataProvider;
use ant\payment\models\Invoice;
use ant\payment\models\BankWireForm;
use ant\payment\models\Payment;

/**
 * Default controller for the `payment` module
 */
class BankWireController extends Controller
{
    public function actionIndex() {
        $dataProvider = new ActiveDataProvider(['query' => Payment::find()->andWhere(['paid_by' => Yii::$app->user->id])]);
        
        return $this->render($this->action->id, [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate($invoice = null) {
        $model = $this->module->getFormModel('bankWire');

        if (isset($invoice)) {
            $invoice = Invoice::decodeId($invoice);
            
            $model->setInvoice($invoice);
            $model->amount = $model->invoice->dueAmount;
            $model->reference = $model->invoice->id.'-'.uniqid();
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', $model->paymentSuccessMessage);
            return $this->redirect(isset($model->redirectUrl) ? $model->redirectUrl : ['payment-success', 'invoice' => $model->invoice->id]);
        }

        return $this->render($this->action->id, [
            'model' => $model,
        ]);
    }
	
	public function actionPaymentSuccess($invoice) {
		$model = Invoice::findOne($invoice);

        return $this->render($this->action->id, [
            'model' => $model,
        ]);
	}
}