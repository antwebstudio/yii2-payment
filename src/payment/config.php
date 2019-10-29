<?php

return [
    'id' => 'payment',
	/*'alias' => [
		'@frontend/modules/payment' => dirname(dirname(dirname(__DIR__))).'/frontend/modules/payment',
		'@ant/payment' => dirname(dirname(dirname(__DIR__))).'/common/modules/payment',
		'@backend/modules/payment' => dirname(dirname(dirname(__DIR__))).'/backend/modules/payment',
	],*/
    'class' => \ant\payment\Module::className(),
	'modules' => [
		'v1' => \ant\payment\api\v1\Module::class,
		'backend' => \ant\payment\backend\Module::class,
	],
    'isCoreModule' => false,
	'depends' => [], // Payment module should not depends on any other module
];