<?php

namespace backend\modules\payment\controllers;

use Yii;
use yii\web\Controller;
use common\modules\payment\models\Payment;
use common\modules\payment\models\Invoice;
use common\modules\payment\models\InvoiceSearch;
use common\modules\payment\models\Order;
use common\modules\payment\components\FaceToFacePaymentMethod;

/**
 * Default controller for the `event` module
 */
class InvoiceController extends Controller
{
	public function behaviors()
	{
		return [
			'verbs' => [
				'class' => \yii\filters\VerbFilter::class,
				'actions' => [
					'pay'  => ['POST'],
				],
			],
		];
	}
	
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionPay($id)
    {
		$model = Invoice::findOne($id);
		
		if (($amount = Yii::$app->request->post('amount')) && ($transactionId = Yii::$app->request->post('transactionId'))) {
			
			$payment = Payment::findByTransactionId($transactionId);

			if (!isset($payment)) {
				$transaction = Yii::$app->db->beginTransaction();

				try {

					$paymentMethod = new FaceToFacePaymentMethod;
					$paymentMethod->setTransactionId($transactionId);
					$paymentMethod->setAmount($amount);
					$record = $paymentMethod->savePaymentRecord($model);

					if ($model->pay($amount)) {
						Yii::$app->session->setFlash('success', 'Payment success');
						$transaction->commit();
					} else {
						throw new \Exception('Unexpected payment error. ');
					}
				} catch (\Exception $ex) {
					$transaction->rollback();
					throw $ex;
				}
			}
		}
		
        return $this->redirect($model->route);
	}

    public function actionView($id){
    	$model = Invoice::find()->andWhere(['id' => $id])->one();
        
    	return $this->render('view', ['model' => $model]);
    }

    public function actionViewByLink($privateSlug){
        $id = Invoice::decodeId($privateSlug);
        $model = Invoice::findOne($id);

        return $this->render('view', [
            'model' => $model,
            'privateSlug' => $privateSlug,
        ]);
    }
	
	public function actionIndex($user = null) {
		$model = new InvoiceSearch;
		$model->userId = $user;
		$dataProvider = $model->search(\Yii::$app->request->queryParams);

		return $this->render($this->action->id, [
			'searchModel' => $model,
			'dataProvider' => $dataProvider,
		]);
	}
}
