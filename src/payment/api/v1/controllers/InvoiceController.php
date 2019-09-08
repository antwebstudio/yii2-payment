<?php

namespace ant\payment\api\v1\controllers;

use Yii;
//use api\v1\modules\miningApp\resources\Payment;

class InvoiceController extends \yii\rest\ActiveController
{
    public $modelClass = 'ant\payment\api\v1\resources\Invoice';
}