<?php

namespace common\modules\payment;

/**
 * payment module definition class
 */
class Module extends \yii\base\Module
{
	public $paymentGateway;
	public $paymentGatewaySandbox;
	public $sandbox = false;
	public $issueToContentType = 'Entry';
	public $issueToColumnName = 'content_id';
	public $issueToAttribute = 'name';
	public $invoiceNumberFormat = '#{id:5}';
	
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
}
