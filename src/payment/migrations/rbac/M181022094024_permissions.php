<?php

namespace ant\payment\migrations\rbac;

use yii\db\Schema;
use ant\rbac\Migration;
use ant\rbac\Role;

class M181022094024_permissions extends Migration
{
	protected $permissions;
	
	public function init() {
		$this->permissions = [
			\ant\payment\controllers\BankWireController::className() => [
				'index' => ['View own bank-wire payment history', [Role::ROLE_USER]],
				'create' => ['Submit bank wire payment', [Role::ROLE_USER]],
				'pay-later' => ['Submit bank wire payment later', [Role::ROLE_USER]],
				'payment-success' => ['Submit bank wire payment success', [Role::ROLE_USER]],
			],
			\ant\payment\controllers\DefaultController::className() => [
				'pay' => ['Make payment', [Role::ROLE_GUEST]],
				'complete-payment' => ['Complete payment made', [Role::ROLE_GUEST]],
			],
			\ant\payment\controllers\InvoiceController::className() => [
				'index' => ['View my invoices', [Role::ROLE_USER]],
				'view-by-link' => ['View invoice by private link', [Role::ROLE_GUEST]],
			],
			// Backend
			\ant\payment\backend\controllers\DefaultController::className() => [
				'index' => ['View all invoices', [Role::ROLE_ADMIN]],
				'view' => ['View invoice detail', [Role::ROLE_ADMIN]],
			],
			\ant\payment\backend\controllers\InvoiceController::class => [
				'index' => ['View all invoices', [Role::ROLE_ADMIN]],
				'view' => ['View invoice detail', [Role::ROLE_ADMIN]],
				'view-by-link' => ['View invoice detail', [Role::ROLE_ADMIN]],
				'pay' => ['Pay invoice', [Role::ROLE_ADMIN]],
				'cancel-pay' => ['Cancel payment for invoice', [Role::ROLE_ADMIN]],
			],
			\ant\payment\backend\controllers\PaymentController::class => [
				'index' => ['View a payment', [Role::ROLE_ADMIN]],
				'approve' => ['Approve a payment', [Role::ROLE_ADMIN]],
				'unapprove' => ['Unapprove a payment', [Role::ROLE_ADMIN]],
			],
			// Models
			\ant\payment\models\Invoice::class => [
				'view-payment-record' => ['View invoice payment records', [Role::ROLE_ADMIN]],
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
