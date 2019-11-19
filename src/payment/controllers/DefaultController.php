<?php

namespace ant\payment\controllers;

use Yii;
use yii\web\NotFoundHttpException;

use ant\payment\components\PaymentComponent;

/**
 * Default controller for the `payment` module
 */
class DefaultController extends \yii\web\Controller
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

    public function actionPay($payId, $type, $payMethod = null, $cancelUrl = '')
    {
		$this->checkComponent();
		
		$payable = $this->getPayableModel($type, $payId);
		$gateway = $this->getPaymentMethod($payMethod, $payable);
		
		Yii::$app->payment->setCancelUrl($cancelUrl);

        if (!$payable->isPaid) {
            if (!$payable->isFree) {
				
				$response = Yii::$app->payment->pay($gateway, $payable);
				
				if ($response->isSuccessful()) {
					return $this->onPaid($payable);
				} else {
					return $this->onError($payable, $response);
				}
            } else {
				if (!$payable->markAsPaid() || !$payable->isPaid) throw new \Exception('Failed to mark payable as paid. ');

				return $this->redirect($this->module->getPaymentSuccessUrl($payable));
            }
        } else {
			// Already paid
			return $this->onPaid($payable);
        }
    }

    public function actionCompletePayment($payId, $type, $payMethod = null, $cancelUrl = '', $backend = false)
    {
		$this->checkComponent();
		
		if ($backend) Yii::$app->session->close();
		
        $payable = $this->getPayableModel($type, $payId);
		$gateway = $this->getPaymentMethod($payMethod, $payable);
		
		Yii::$app->payment->setCancelUrl($cancelUrl);

		if (!$payable->isPaid) {

			if ($backend) {
				$response = Yii::$app->payment->completePaymentFromBackend($gateway, $payable);
			} else {
				$response = Yii::$app->payment->completePayment($gateway, $payable);
			}
			
			if ($response->isSuccessful()) {
				return $this->onPaid($payable);
			} else {
				return $this->onError($payable, $response);
			}
		} else {
			// Invoice paid
			return $this->onPaid($payable);
		}
	}
	
	protected function checkComponent() {
		if (!isset(Yii::$app->payment)) {
			throw new \Exception('Payment component is not setup. Please setup using ant\payment\components\PaymentComponent.');		
		}
	}
	
	protected function onError($payable, $response) {
		return $this->render('payment-error', [
			'response' => $response, 
			'url' => $this->module->getPaymentErrorUrl($payable)
		]);
	}

    protected function onPaid($payable)
    {
		if (!$payable->isPaid) throw new \Exception('Payment failed for unknown reason. ');
		
		// Aware that invoice maybe already marked as paid by a backend url call when the web browser is redirected back to event.my after payment.
		// Hence if we just render invoice paid message, it will be weird as "Invoice is already paid" message will show, and "Payment succesful" message will never shown.
		return $this->redirect($this->module->getPaymentSuccessUrl($payable));
	}

    protected function getPaymentMethod($type, $payable)
    {
		$gateway = Yii::$app->payment->getPaymentMethod($type);
		
		if (!isset($gateway) || !$gateway->isEnabledFor($payable)) {
			throw new \Exception('Payment method "'.$type.'" is not exist or not allowed. ');
		}
		
		return $gateway;
    }

    protected function getPayableModel($type, $payId)
    {
		$payable = Yii::$app->payment->getPayableModel($type, $payId);
		
		if(!isset($payable) || ($payable instanceof \ant\interfaces\Expirable && $payable->isExpired)) {
			throw new NotFoundHttpException('Page not found or expired. ');
		}
		
		return $payable;
    }
}
