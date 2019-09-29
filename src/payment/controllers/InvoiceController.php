<?php
namespace ant\payment\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\HttpException;

use common\modules\payment\models\Payable;
use common\modules\payment\models\PayableItem;
use common\modules\payment\models\PayableCallBack;

use common\modules\payment\models\Order;
use common\modules\payment\components\PayPalExpressGateway;
use common\modules\payment\models\Invoice;
use common\modules\payment\models\InvoiceItem;

class InvoiceController extends \yii\web\Controller {
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
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