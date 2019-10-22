<?php
namespace common\modules\payment\components;

use Yii;
use Omnipay\Omnipay;
use yii\helpers\Url;
use yii\base\Component;

abstract class PaymentMethod extends \ant\payment\components\PaymentMethod implements PaymentMethodInterface {
	public function __construct() {
		if (YII_DEBUG) throw new \Exception('DEPRECATED');
		$this->initGateway($config);
		return parent::__construct($config);
	}
}
