<?php
namespace backend\modules\payment\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\HttpException;
use yii\data\ActiveDataProvider;
use ant\payment\models\Payment;

class BankWireController extends Controller
{
    public function actionIndex() {
        $dataProvider = new ActiveDataProvider([
            'query' => Payment::find()->andWhere(['payment_gateway' => 'ant\payment\components\BankWirePaymentMethod']),
        ]);

        return $this->render($this->action->id, [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionApprove($id) {
        $transaction = Yii::$app->db->beginTransaction();

        $payment = Payment::findOne($id);
        $payment->is_valid = 1;

        if ($payment->save()) {
            $payment->trigger(Payment::EVENT_APPROVED);
            $transaction->commit();

            return $this->redirect(['index']);
        }
    }

    public function actionUnapprove($id) {
        $transaction = Yii::$app->db->beginTransaction();
        
        $payment = Payment::findOne($id);
        $payment->is_valid = 0;

        if ($payment->save()) {
            $payment->trigger(Payment::EVENT_UNAPPROVED);
            $transaction->commit();

            return $this->redirect(['index']);
        }
    }
}