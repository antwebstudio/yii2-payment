<?php

namespace frontend\modules\payment;

use common\modules\payment\models\Payable;

/**
 * payment module definition class
 */
class Module extends \common\modules\payment\Module
{
	const SESSION_PAYMENT_SUCCESS_URL = 'paymentSuccessUrl';
	
	public $sandbox = false;
	public $paymentGateway;
	public $paymentGatewaySandbox;
	public $issueToContentType = 'Entry';
	public $issueToColumnName = 'content_id';
	public $issueToAttribute = 'name';

	public $paymentPageUrl;
	public $successUrl = '/order/default/success';
	public $deductQuantity = true;
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'frontend\modules\payment\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
	
	public function getPaymentPageUrl($payableModel) {
		if (isset($this->paymentPageUrl)) {
			if (is_callable($this->paymentPageUrl)) {
				return call_user_func_array($this->paymentPageUrl, [$payableModel]);
			} else {
				return $this->paymentPageUrl;
			}
		}
		if (YII_DEBUG) throw new \Exception('Please set the "paymentPageUrl" for payment module.');
		
		return \Yii::$app->homeUrl;
	}
	
	public function getPaymentErrorUrl($payableModel) {
		if (is_callable($this->successUrl)) {
			return call_user_func_array($this->successUrl, [$payableModel]);
		} else {
			return [$this->successUrl, 'o' => $payableModel->formatted_id];
		}
	}
	
	public function setPaymentSuccessUrl($url) {
		return \Yii::$app->session->set(self::SESSION_PAYMENT_SUCCESS_URL, $url);
	}
	
	public function getPaymentSuccessUrl($payableModel) {
		if (is_callable($this->successUrl)) {
			return call_user_func_array($this->successUrl, [$payableModel]);
		} else {
			return \Yii::$app->session->get(self::SESSION_PAYMENT_SUCCESS_URL, [$this->successUrl, 'o' => $payableModel->formatted_id]);
		}
	}
	
	public function getPayableModel($payType, $payId)
    {
        if (!$payableClass = $this->getPayableClass($payType)) {
			return false;
		}

        return $payableClass::findOne($payId);
    }

    protected function getPayableClass($payType = false)
    {
        //default pay option
        if(!$payType) return Invoice::className();

		//extra pay option
		if (isset($this->extraPayOptions[$payType])) {
			$payable = $this->extraPayOptions[$payType];
		} else {
			throw new \Exception('Payable model type "'.$payType.'" not defined. ');
		}
		
        return ($payable && (new $payable instanceof Payable)) ? $payable : false;
    }

	protected function getExtraPayOptions()
	{
		return
		[
			'invoice' => '\common\modules\payment\models\Invoice',
			'order' => '\common\modules\order\models\Order'
		];
	}
}
