<?php
namespace ant\payment\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\HttpException;

use ant\payment\models\Payable;
use ant\payment\models\PayableItem;
use ant\payment\models\PayableCallBack;

use ant\payment\models\Order;
use ant\payment\components\PayPalExpressGateway;
use ant\payment\models\Invoice;
use ant\payment\models\InvoiceItem;
use ant\payment\models\InvoiceSearch;
use ant\organization\models\Organization;

class InvoiceController extends \yii\web\Controller {
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
		$organization = Organization::find()->haveCollaborator(Yii::$app->user->id)->one();
		$model = new InvoiceSearch;
		$dataProvider = $model->search(Yii::$app->request->queryParams);
		$dataProvider->sort = ['defaultOrder' => ['issue_date'=>SORT_DESC]];
		$dataProvider->query->andWhere([
			'organization_id' => $organization->id,
		]);
		
        return $this->render($this->action->id, [
			'dataProvider' => $dataProvider,
		]);
    }

    public function actionViewByLink($privateSlug) {
        $id = Invoice::decodeId($privateSlug);
        $model = Invoice::findOne($id);

        return $this->render('view', [
            'model' => $model,
            'privateSlug' => $privateSlug,
        ]);
    }
	
	public function actionUpdate() {
		return $this->render($this->action->id, [
		]);
	}
}