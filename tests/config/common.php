<?php

return [
	'id' => 'app-test',
	'basePath' => dirname(__DIR__),
	'aliases' => [
		'ant' => dirname(dirname(__DIR__)).'/src',
		'api' => dirname(dirname(__DIR__)).'/src/api',
		'common/config' => __DIR__, // dirname(dirname(__DIR__)).'/vendor/inspirenmy/yii2-core/src/common/config',
		'vendor' => dirname(dirname(__DIR__)).'/vendor',
		'@common/migrations' => '@vendor/inspirenmy/yii2-core/src/common/migrations',
		'common/modules/moduleManager' => dirname(dirname(__DIR__)).'/vendor/inspirenmy/yii2-core/src/common/modules/moduleManager',
        '@common/rbac/views' => '@vendor/inspirenmy/yii2-core/src/common/rbac/views',
	],
    'components' => [
        'mutex' => [
            'class' => 'yii\mutex\MysqlMutex',
        ],
		'payment' => [
			'class' => 'ant\payment\components\PaymentComponent',
			'paymentGateway' => [
                'ipay88' => [
					'class' => 'ant\payment\components\IPay88PaymentMethod',
					'config' => [
						'merchantCode' => 'M09111',
						'merchantKey' => 'tFgrFE0vUR',
						'sandboxUrl' => ['/sandbox', 'sandbox' => 'ipay88'],
						'sandboxRequeryUrl' => ['/sandbox', 'sandbox' => 'ipay88', 'requery' => 1],
						'requeryNeeded' => false,
					],
					'overrideMethods' => [
						'getPaymentDataForGateway' => function($payable, $paymentGateway) {
							$paymentId = 2;
							
							$returnUrl = \Yii::$app->payment->getReturnUrl($paymentGateway->name);
							$cancelUrl = \Yii::$app->payment->getCancelUrl($paymentGateway->name);

							$cancelUrlParams = $returnUrlParams = \Yii::$app->request->get();
							array_unshift($returnUrlParams, $returnUrl);
							array_unshift($cancelUrlParams, $cancelUrl);
							$backendUrlParams = $returnUrlParams;
							$backendUrlParams['backend']  = 1;
							
							return [
								'amount' => $payable->getDueAmount(),
								'currency' => $payable->getCurrency(),
								'expires_in' =>  time() + 10,
								'card' => [
									'billingName' => 'test', // $payableModel->billedTo->contactName,
									'email' => 'test@example.com', // $payableModel->billedTo->email,
									'number' => '01640612345', // $payableModel->billedTo->contact_number,
								],
								'paymentId' => $paymentId,
								'description' => 'Event Registration Fee',
								'transactionId' => $payable->getReference(),
								// 'card' => $formData,

								'returnUrl' => 'localhost',
								'cancelUrl' => '',
								'backendUrl' => '',
							];
						},
					],
                ],
			],
		],
        'i18n' => [
            'translations' => [
                '*'=> [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath'=>'@common/messages',
                ],
            ],
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;port=3306;dbname=test_test',
            'username' => 'root',
            'password' => 'root',
            'tablePrefix' => '',
            'charset' => 'utf8',
        ],
        'moduleManager' => [
            'class' => 'ant\moduleManager\ModuleManager',
			'moduleAutoloadPaths' => [
				'@ant', 
				'@vendor/inspirenmy/yii2-ecommerce/src/common/modules', 
				'@vendor/inspirenmy/yii2-user/src/common/modules',
				'@vendor/inspirenmy/yii2-core/src/common/modules',
			],
        ],
		// Needed for rbca migration, else error occured when run yii migrate
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'defaultRoles' => [\ant\rbac\Role::ROLE_GUEST, \ant\rbac\Role::ROLE_USER],
        ],
        'user' => [
			'class' => 'yii\web\User',
            'identityClass' => 'ant\user\models\User',
        ],
	],
	'controllerMap' => [
		'module' => [
			'class' => 'ant\moduleManager\console\controllers\DefaultController',
		],
		'migrate' => [
			'class' => 'ant\moduleManager\console\controllers\MigrateController',
            'migrationPath' => [
                '@common/migrations/db',
                '@yii/rbac/migrations',
				'@tests/migrations/db',
            ],
            'migrationNamespaces' => [
                'yii\queue\db\migrations',
				'ant\moduleManager\migrations\db',
			],
            'migrationTable' => '{{%system_db_migration}}'
		],
		'rbac-migrate' => [
			'class' => 'ant\moduleManager\console\controllers\RbacMigrateController',
            'migrationPath' => [
                '@common/migrations/rbac',
            ],
            'migrationTable' => '{{%system_rbac_migration}}',
            'migrationNamespaces' => [
                'ant\moduleManager\migrations\rbac',
			],
            'templateFile' => '@common/rbac/views/migration.php'
		],
	],
];