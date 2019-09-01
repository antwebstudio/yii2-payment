<?php

return [
    'id' => 'payment',
	'alias' => [
		'@frontend/modules/payment' => dirname(dirname(dirname(__DIR__))).'/frontend/modules/payment',
		'@common/modules/payment' => dirname(dirname(dirname(__DIR__))).'/common/modules/payment',
		'@backend/modules/payment' => dirname(dirname(dirname(__DIR__))).'/backend/modules/payment',
	],
    'class' => \frontend\modules\payment\Module::className(),
    'isCoreModule' => false,
	'depends' => [], // Payment module should not depends on any other module
];
?>