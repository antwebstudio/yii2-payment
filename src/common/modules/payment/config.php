<?php

return [
    'id' => 'payment',
	'namespace' => 'common\modules\payment',
    'class' => \common\modules\payment\Module::className(),
    'isCoreModule' => false,
	'modules' => [
		'v1' => \ant\payment\api\v1\Module::class,
	],
	'depends' => ['contact'], // Payment module should not depends on any other module
];