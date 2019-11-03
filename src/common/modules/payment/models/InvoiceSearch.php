<?php

namespace common\modules\payment\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\payment\models\Invoice;
use ant\contact\models\Contact;

/**
 * InvoiceSearch represents the model behind the search form about `common\modules\payment\models\Invoice`.
 */
class InvoiceSearch extends \common\modules\payment\models\InvoiceSearch
{
	
	public function init() {
		if (YII_DEBUG) throw new \Exception('DEPRECATED');
	}
}