<?php

namespace ant\payment;

/**
 * payment module definition class
 */
class Module extends \yii\base\Module
{
	public $invoiceNumberFormat = '#{id:5}';
	
	/*public $sandbox = false;
	public $paymentGateway;
	public $paymentGatewaySandbox;
	public $issueToContentType = 'Entry';
	public $issueToColumnName = 'content_id';
	public $issueToAttribute = 'name';

	public $paymentPageUrl;*/
	//public $successUrl = '/order/default/success';
	//public $deductQuantity = true;
	
	public function behaviors() {
		return [
			[
				'class' => \common\behaviors\ConfigurableModuleBehavior::className(),
				'formModels' => [
					'bankWire' => ['class' => \common\modules\payment\models\BankWireForm::className()],
					/*'on '.\common\base\FormModel::EVENT_AFTER_SAVE => function($event) {
						$formModel = $event->sender;
						$formModel->sendNotificationEmailToAdmin();
					}*/
				],
			],
		];
	}

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
	
	public function getPaymentPageUrl($payableModel) {
		return \Yii::$app->payment->getPaymentCancelUrl($payableModel);
	}
	
	public function getPaymentErrorUrl($payableModel) {
		return \Yii::$app->payment->getPaymentErrorUrl($payableModel);
	}
	
	public function setPaymentSuccessUrl($url) {
		return \Yii::$app->payment->setPaymentSuccessUrl($url);
	}
	
	public function getPaymentSuccessUrl($payableModel) {
		return \Yii::$app->payment->getPaymentSuccessUrl($payableModel);
	}
	
	public function getPayableModel($payType, $payId) {
		return \Yii::$app->payment->getPayableModel($payType, $payId);
    }
}
