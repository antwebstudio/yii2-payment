<?php
namespace ant\payment\widgets;

use yii\base\Widget;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use ant\payment\models\Payment;
use yii\data\ActiveDataProvider;
class PaymentSummary extends Widget{
	public $model;
    public $invoiceId;
    public $invoice_id;
	public $viewFile = 'payment-summary';
	
	public function init()
	{
        parent::init();
	}

	public function run(){	
		if (!isset($this->invoiceId) && isset($this->invoice_id)) {
			$this->invoiceId = $this->invoice_id;
			if (YII_DEBUG) throw new \Exception('Please use invoiceId instead of invoice_id. '); // 2020-04-12
		}
        $model = Payment::find()->andWhere(['invoice_id' => $this->invoiceId]);
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
        return $this->render($this->viewFile, ['dataProvider' => $dataProvider]);
    }
}


