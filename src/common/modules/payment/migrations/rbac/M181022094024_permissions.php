<?php

namespace common\modules\payment\migrations\rbac;

use yii\db\Schema;
use common\rbac\Migration;
use common\rbac\Role;

class M181022094024_permissions extends Migration
{
	protected $permissions;
	
	public function init() {
		$this->permissions = [
			\frontend\modules\payment\controllers\BankWireController::className() => [
				'index' => ['View own bank-wire payment history', [Role::ROLE_USER]],
				'create' => ['Submit bank wire payment', [Role::ROLE_USER]],
			],
			\frontend\modules\payment\controllers\DefaultController::className() => [
				'pay' => ['Make payment', [Role::ROLE_GUEST]],
				'complete-payment' => ['Complete payment made', [Role::ROLE_GUEST]],
			],
			\frontend\modules\payment\controllers\InvoiceController::className() => [
				'view-by-link' => ['View invoice by private link', [Role::ROLE_GUEST]],
			],
			\backend\modules\payment\controllers\DefaultController::className() => [
				'index' => ['View all invoices', [Role::ROLE_ADMIN]],
				'view' => ['View invoice detail', [Role::ROLE_ADMIN]],
			],
			\backend\modules\payment\controllers\InvoiceController::class => [
				'index' => ['View all invoices', [Role::ROLE_ADMIN]],
				'view' => ['View invoice detail', [Role::ROLE_ADMIN]],
				'pay' => ['Pay invoice', [Role::ROLE_ADMIN]],
				'cancel-pay' => ['Cancel payment for invoice', [Role::ROLE_ADMIN]],
			],
			\backend\modules\payment\controllers\PaymentController::class => [
				'approve' => ['Approve a payment', [Role::ROLE_ADMIN]],
				'unapprove' => ['Unapprove a payment', [Role::ROLE_ADMIN]],
			],
		];
		
		parent::init();
	}
	
	public function up()
    {
		$this->addAllPermissions($this->permissions);
    }

    public function down()
    {
		$this->removeAllPermissions($this->permissions);
    }
}
