<?php
namespace ant\payment\controllers;

class InvoiceController extends \yii\web\Controller {
	public function actionUpdate() {
		return $this->render($this->action->id, [
		]);
	}
}