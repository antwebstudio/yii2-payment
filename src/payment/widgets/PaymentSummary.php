<?php
namespace ant\payment\widgets;

use yii\base\Widget;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use ant\payment\models\Payment;
use yii\data\ActiveDataProvider;
class PaymentSummary extends Widget{
	public $model;
    public $invoice_id;
	
	public function init()
	{
        parent::init();
	}

	public function run(){	

        $model = Payment::find()->andWhere(['invoice_id' => $this->invoice_id]);
        // echo "<pre>";
        // print_r($model);
        // echo "</pre>";
        //$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider = new ActiveDataProvider([
        'query' => $model,
        'pagination' => [
            'pageSize' => 10,
        ],
        ]);
        return $this->render('paymentSummary', ['dataProvider' => $dataProvider]);
    }
}


