<?php

namespace ant\payment\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\HttpException;

use ant\payment\models\Payable;
use ant\payment\models\Billable;
use ant\payment\models\PayableItem;

use ant\payment\models\Order;
use ant\payment\components\PayPalExpressGateway;
use ant\payment\components\IPay88Gateway;
use ant\payment\models\Invoice;
use ant\payment\models\InvoiceItem;

/**
 * Default controller for the `payment` module
 */
class DefaultController extends Controller
{
	public $enableCsrfValidation = false;

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionPay($payId, $type = false, $payMethod = false, $cancelUrl = '')
    {
		if (!isset(\Yii::$app->payment)) throw new \Exception('Payment component is not setup. Please setup using ant\payment\components\PaymentComponent.');		
		
		$payableModel = $this->getPayableModel($type, $payId);
		
		if ($payableModel instanceof \ant\interfaces\Expirable && $payableModel->isExpired) throw new \Exception('The payable is expired. ');
		
		\Yii::$app->payment->setCancelUrl($cancelUrl);

        if (!$payableModel->isPaid) {
            if (!$payableModel->isFree) {
				$transaction = Yii::$app->db->beginTransaction();
                // Payment gateway
                $gateway = $this->getPaymentMethod($payMethod, $payableModel);
                $response = $gateway->purchase($gateway->getPaymentDataForGateway($payableModel));

				$return = $this->handleResponse($gateway, $response, $payableModel);

				$transaction->commit();
				return $return;
            } else {
				if (!$payableModel->markAsPaid() || !$payableModel->isPaid) throw new \Exception('Failed to mark order as paid. ');

				return $this->redirect($this->module->getPaymentSuccessUrl($payableModel));
				
            }
        } else {
			// Already paid
			return $this->onPaid($payableModel);
        }
    }

    public function actionCompletePayment($payId, $type = false, $payMethod = false, $cancelUrl = '', $backend = false)
    {
		if ($backend) \Yii::$app->session->close();
		
        $payableModel = $this->getPayableModel($type, $payId);
		
		\Yii::$app->payment->setCancelUrl($cancelUrl);

        //$invoice = $this->getInvoice($payableModel);

		//if (!isset($invoice)) throw new \Exception('Invalid Invoice.');

		if (!$payableModel->isPaid) {
			$gateway = $this->getPaymentMethod($payMethod, $payableModel);

			$response = $gateway->completePurchase($gateway->getPaymentDataForGateway($payableModel));

			$return = $this->handleResponse($gateway, $response, $payableModel, $backend);
			
			if ($backend && $response->isSuccessful()) die('RECEIVEOK');
			
			return $return;
		} else {
			// Invoice paid
			return $this->onPaid($payableModel);
		}
	}

    protected function handleResponse($gateway, $response, $payableModel, $backend = false)
    {
		if ($response->isRedirect()) {
            // redirect to offsite payment gateway
            $response->redirect();
		} elseif ($response->isSuccessful()) {
            return $this->onPaymentSuccessful($gateway, $payableModel, $backend);
		} else {
			// payment failed: display message to customer
			return $this->onPaymentError($gateway, $response, $payableModel);
		}
	}

    protected function onPaymentError($gateway, $response, $payableModel)
    {
		
		$payment = \ant\payment\models\Payment::findOne(['transaction_id' => $gateway->paymentRecord->transaction_id]);
		
		if (!isset($payment)) {
			$invoice = $this->getInvoice($payableModel);
			$payment = $gateway->paymentRecord;
			$payment->invoice_id = $invoice->id;
			if (!$payment->save()) throw new \Exception('Payment record failed to be saved. '.(YII_DEBUG ? Html::errorSummary($payment).print_r($payment,1) : ''));

			// Customer Cancel Transaction will also come to this action, hence should not throw exception.
		}
		return $this->render('payment-error', ['response' => $response, 'url' => $this->module->getPaymentErrorUrl($payableModel)]);
		
		//throw new \yii\web\HttpException(500, $response->getMessage());
    }

    protected function onPaid($payableModel)
    {
		if (!$payableModel->isPaid) throw new \Exception('Payment failed for unknown reason. ');
		
		// Aware that invoice maybe already marked as paid by a backend url call when the web browser is redirected back to event.my after payment.
		// Hence if we just render invoice paid message, it will be weird as "Invoice is already paid" message will show, and "Payment succesful" message will never shown.
		return $this->redirect($this->module->getPaymentSuccessUrl($payableModel));
	}

    protected function onPaymentSuccessful($gateway, $payableModel, $backend = false)
    {
		$payment = \ant\payment\models\Payment::findOne(['transaction_id' => $gateway->paymentRecord->transaction_id]);
		
		if (!isset($payment)) {
			$invoice = $this->getInvoice($payableModel);
			$payment = $gateway->paymentRecord;
			$payment->invoice_id = $invoice->id;
			// $payment->data = \Yii::$app->request->post(); // Some data of payment gateway cannot get from $_POST
			
			if ($backend) $payment->backend_update = 1;

			if (!$payment->save()) throw new \Exception('Payment record failed to be saved. '.(YII_DEBUG ? Html::errorSummary($payment) : ''));
		}
        $payableModel->pay($payment->amount);
		
        return $this->onPaid($payableModel);
	}

    protected function getPaymentMethod($type, $payableModel)
    {
		$type = strlen($type) ? $type : 'ipay88';
		$gateway = \Yii::$app->payment->getPaymentMethod($type);
		if (!$gateway->isEnabledFor($payableModel)) throw new \Exception('Payment method "'.$type.'" is not allowed. ');
		
		return $gateway;
    }

    protected function getPayableModel($type, $payId)
    {
		$payableModel = $this->module->getPayableModel($type, $payId);
		
		if(!isset($payableModel)) {
			throw new HttpException(404, YII_DEBUG ? 'Payable model not found' : 'Page not found');
		}
		return $payableModel;
    }

    protected function getInvoice($payableModel)
    {
		// TODO: try to generalize this using payable interface
        if($payableModel instanceof Invoice) {
            return $payableModel;
		} else if(isset($payableModel->invoice)) {
			return $payableModel->invoice;
        } else if($payableModel instanceof Billable) {
            return Invoice::createFromBillableModel($payableModel, Yii::$app->user->identity);
        }
    }
}
