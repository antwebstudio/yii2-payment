<?php

return [
    'id' => 'payment',
	'namespace' => 'common\modules\payment',
    'class' => \common\modules\payment\Module::className(),
    'isCoreModule' => false,
	'depends' => ['contact', 'cart'], // Payment module should not depends on any other module
];
?>