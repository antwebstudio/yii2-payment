<?php

namespace ant\payment\backend\controllers;

use Yii;
use ant\payment\models\Payment;

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
			$transaction = Yii::$app->db->beginTransaction();
			
			try {
				if ($model->approve()->save()) {
					$model->invoice->pay($model->amount)->save();
					Yii::$app->session->setFlash('success', 'Payment succesfully approved. ');
				} else {
					throw new \Exception(print_r($model->errors, 1));
				}
				$transaction->commit();
			} catch (\Exception $ex) {
				$transaction->rollback();
				if (YII_DEBUG) throw $ex;
				
				Yii::$app->session->setFlash('error', 'Payment failed to be approved. ');
			}
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionUnapprove($id) {
        $model = Payment::findOne($id);
        
        if ($model->status == Payment::STATUS_SUCCESS) {
			$transaction = Yii::$app->db->beginTransaction();
			
			try {
				if ($model->unapprove()->save()) {
					$model->invoice->pay(0 - $model->amount)->save();
					Yii::$app->session->setFlash('success', 'Payment succesfully unapproved. ');
				} else {
					throw new \Exception(print_r($model->errors, 1));
				}
				$transaction->commit();
			} catch (\Exception $ex) {
				$transaction->rollback();
				if (YII_DEBUG) throw $ex;
				
				Yii::$app->session->setFlash('error', 'Payment failed to be unapproved. ');
			}
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
}