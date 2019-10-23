<?php

return [
    'id' => 'payment',
	/*'alias' => [
		'@frontend/modules/payment' => dirname(dirname(dirname(__DIR__))).'/frontend/modules/payment',
		'@ant/payment' => dirname(dirname(dirname(__DIR__))).'/common/modules/payment',
		'@backend/modules/payment' => dirname(dirname(dirname(__DIR__))).'/backend/modules/payment',
	],*/
    'class' => \ant\payment\backend\Module::className(),
    'isCoreModule' => false,
];