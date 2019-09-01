<?php

namespace backend\modules\payment\controllers;

use Yii;
use common\modules\payment\models\Payment;

/**
 * Default controller for the `event` module
 */
class PaymentController extends \yii\web\Controller {
    public function behaviors() {
        return [
            [
                'class' => 'yii\filters\VerbFilter',
                'actions' => [
                    'approve' => ['POST'],
                    'unapprove' => ['POST'],
                ],
            ]
        ];
    }

    public function actionApprove($id) {
        $model = Payment::findOne($id);
        
        if ($model->status != Payment::STATUS_SUCCESS) {
            if ($model->approve()->save()) {
                $model->invoice->pay($model->amount);
                Yii::$app->session->setFlash('success', 'Payment succesfully approved. ');
            } else {
                Yii::$app->session->setFlash('error', 'Payment failed to be approved. ');
            }
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionUnapprove($id) {
        $model = Payment::findOne($id);
        
        if ($model->status == Payment::STATUS_SUCCESS) {
            if ($model->unapprove()->save()) {
                $model->invoice->pay(0 - $model->amount);
                Yii::$app->session->setFlash('success', 'Payment succesfully unapproved. ');
            } else {
                Yii::$app->session->setFlash('error', 'Payment failed to be unapproved. ');
            }
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
}