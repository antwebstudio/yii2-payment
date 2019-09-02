<?php

namespace backend\modules\payment\controllers;

use Yii;
use yii\web\Controller;
use common\modules\payment\models\Invoice;
use common\modules\payment\models\InvoiceSearch;
use common\modules\payment\models\Order;


/**
 * Default controller for the `event` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
    	$model = Invoice::find()->all();
    	$searchModel = new InvoiceSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        // echo "<pre>";
        // foreach ($model as $key => $value) {
        //     print_r($value->billedTo->contact_name);
        // }
        // echo "</pre>";
        // die;
        return $this->render('index',['model' => $model, 'dataProvider' => $dataProvider, 'searchModel' =>$searchModel]);
    }

    public function actionView($id){
        if (YII_DEBUG) throw new \Exception('DEPRECATED');
        
    	return $this->redirect('/payment/invoice/view', ['id' => $id]);
    }

    public function actionUpdate(){

    }

    public function actionDelete(){

    }
}