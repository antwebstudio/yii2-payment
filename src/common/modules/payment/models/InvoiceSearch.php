<?php

namespace ant\payment\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use ant\payment\models\Invoice;
use ant\contact\models\Contact;

/**
 * InvoiceSearch represents the model behind the search form about `ant\payment\models\Invoice`.
 */
class InvoiceSearch extends \ant\payment\models\InvoiceSearch
{
	
	public function init() {
		if (YII_DEBUG) throw new \Exception('DEPRECATED');
	}
}