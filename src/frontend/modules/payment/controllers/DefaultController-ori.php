<?php

namespace frontend\modules\payment\controllers;

use yii\web\Controller;
use yii\helpers\Url;
use yii\helpers\Html;
use common\modules\payment\models\Invoice;
use common\modules\payment\components\PayPalExpressGateway;

/**
 * Default controller for the `payment` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

	public function actionPay($invoiceId, $cancelUrl = '') {
		// Get invoice
		$invoice = Invoice::find($invoiceId)->one();

		if (!$invoice->isPaid) {
			// Payment gateway
			$gateway = $this->getPaymentGateway();
			$response = $gateway->purchase($this->getPaymentDataForGateway($invoice, $cancelUrl));

			return $this->handleResponse($gateway, $response, $invoice);
		} else {
			// Invoice paid
			return $this->onInvoicePaid($invoice);
		}
	}

	public function actionCompletePayment($invoiceId) {
		$invoice = Invoice::find($invoiceId)->one();

		if (!isset($invoice)) throw new \Exception('Invalid Invoice.');

		if (!$invoice->isPaid) {
			$gateway = $this->getPaymentGateway();
			$response = $gateway->completePurchase($this->getPaymentDataForGateway($invoice));

			return $this->handleResponse($gateway, $response, $invoice);
		} else {
			// Invoice paid
			return $this->onInvoicePaid($invoice);
		}
	}

	protected function onInvoicePaid($invoice) {
		return $this->render('invoice-paid', ['invoice' => $invoice]);
	}

	protected function onPaymentSuccessful($gateway, $invoice) {
		$amount = $gateway->getAmount();

		$payment = $gateway->payment;
		$payment->invoice_id = $invoice->id;
		$payment->data = json_encode($_POST);

		if (!$payment->save()) throw new \Exception('Payment record failed to be saved. '.(YII_DEBUG ? Html::errorSummary($payment) : ''));

		$invoice->pay($amount);
		$invoice->save();

		return $this->render('payment-success', ['invoice' => $invoice]);
	}

	protected function onPaymentError($gateway, $response, $invoice) {
		$payment = $gateway->payment;
		$payment->invoice_id = $invoice->id;
		if (!$payment->save()) throw new \Exception('Payment record failed to be saved. '.(YII_DEBUG ? Html::errorSummary($payment) : ''));

		throw new \yii\web\HttpException(500, $response->getMessage());
	}

	protected function handleResponse($gateway, $response, $invoice) {
		if ($response->isSuccessful()) {
			// payment was successful: update database
			return $this->onPaymentSuccessful($gateway, $invoice);
		} elseif ($response->isRedirect()) {
			// redirect to offsite payment gateway
			$response->redirect();
		} else {
			// payment failed: display message to customer
			return $this->onPaymentError($gateway, $response, $invoice);
		}
	}

	protected function getPaymentDataForGateway($invoice, $cancelUrl = null) {

		if (!isset($invoice)) throw new \yii\web\HttpException(404, 'Invoice not exist.');

		$returnUrlParams = $_GET;
		array_unshift($returnUrlParams, '/payment/default/complete-payment');
		$cancelUrlParams = $_GET;
		array_unshift($cancelUrlParams, $cancelUrl);

		return [
			'amount' => $invoice->getDueAmount(),
			'currency' => $invoice->getCurrency(),
			// 'card' => $formData,

			'returnUrl' => Url::to($returnUrlParams, true),
			'cancelUrl' => Url::to($cancelUrlParams, true),
		];
	}

	protected function getPaymentGateway() {
		$gateway = new PayPalExpressGateway();

		if ($this->module->sandbox) {
			$config = $this->module->paymentGatewaySandbox;
			$gateway->setUsername($config['paypal']['username']);
			$gateway->setPassword($config['paypal']['password']);
			$gateway->setSignature($config['paypal']['signature']);
			$gateway->setTestMode(true);
		} else {
			$config = $this->module->paymentGateway;
			$gateway->setUsername($config['paypal']['username']);
			$gateway->setPassword($config['paypal']['password']);
			$gateway->setSignature($config['paypal']['signature']);
		}
		return $gateway;
	}

	public function actionConfirm() {
		$paymentGateway = new IPayGateway;
		$paymentGateway->subjectId = $paymentGateway->getResponse('RefNo');
		if (!$paymentGateway->save()) throw new \Exception('Payment gateway failed to handle the payment. ');

		if ($paymentGateway->isPaymentValid()) {
			Invoice::updateAll(['id' => $paymentGateway->subjectId], [
				'paid_amount' => new \yii\db\Expression('(`paid_amount` + :amount)', [':amount' => $paymentGateway->amount]),
				'status' => new \yii\db\Expression('IF(`paid_amount` >= `total_amount`, '.Invoice::STATUS_PAID.', `status`)'),
			]);
			//Yii::app()->user->setFlash('success', 'Thank you! Payment was successfully made. ');
		} else {
			//Yii::app()->user->setFlash('error', 'Sorry, payment was failed, please try again. ');
		}
		$this->redirect(['/payment/invoice']);
	}
}
