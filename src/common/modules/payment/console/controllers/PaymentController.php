<?php
namespace common\modules\payment\console\controllers;

use Yii;
use yii\helpers\Url;
use yii\helpers\Console;
use yii\helpers\Html;
use console\components\Controller;
use common\modules\order\models\Order;
use common\modules\payment\models\Invoice;

class PaymentController extends Controller
{
	public function actionValidateSignature($merchantKey, $merchantCode, $refNo, $amount, $currency) {
		$amount = str_replace([',', '.'], '', $amount);
        $paramsInArray = [$merchantKey, $merchantCode, $refNo, $amount, $currency];
        Console::Output('Signature: '. $this->createSignatureFromString(implode('', $paramsInArray)));
	}
	
	protected function createSignatureFromString($fullStringToHash)
    {
        return base64_encode($this->hex2bin(sha1($fullStringToHash)));
    }
	
	private function hex2bin($hexSource)
    {
        $bin = '';
        for ($i = 0; $i < strlen($hexSource); $i = $i + 2) {
            $bin .= chr(hexdec(substr($hexSource, $i, 2)));
        }
        return $bin;
    }
	
	public function actionRequery($ref = null) {
		if (isset($ref)) {
			$order = \common\modules\order\models\Order::findOne($ref);
			$orders = [$order];
		} else {
			$orders = \common\modules\order\models\Order::find()->orderBy('created_at DESC')->where(['status' => Order::STATUS_ACTIVE])->limit(50)->all();
		}
		
		if (isset($orders)) {
		
			foreach ($orders as $order) {
				Console::output('Order ID: '.$order->id);
				$this->processOrder($order);
				Console::output("\n");
			}
		} else {
			die('Order not found. ');
		}
	}
	
	/*protected function addSuccessfulPaymentRecord($gateway, $order) {
		$adapter = new \common\modules\payment\components\PaymentRecordAdapter;
		
		$adapter->payment_gateway = $gateway::className();
		$adapter->currency = $order->invoice->currency;
		$adapter->amount = $order->getCalculatedTotalAmount(); // $gateway->response->getAmount();
		$adapter->is_valid = 1; // Should not use status code, 1 - valid (while status 0 - valid)
		//$adapter->transaction_id = $this->_response->getTransactionReference();
		
		$payment = $adapter->getPaymentRecord();
		$payment->invoice_id = $order->invoice->id;
		$payment->data = '[]';
		$payment->paid_by = $order->created_by;
		$payment->status = 1;
		
		$payment->detachBehavior(\yii\behaviors\BlameableBehavior::className());
		
		if (!$payment->save()) throw new \Exception(Html::errorSummary($payment));
	}*/
	
	protected function requery($ref, $amount) {
		$gateway = \Yii::$app->payment->getPaymentMethod('ipay88');
		$response = $gateway->requery([
			'amount' => $amount,
			'id' => $ref,
		]);
		
		Console::output('Amount: '.$amount);
		Console::output('Ref: '.$ref);
		
		return $response;
	}
	
	protected function processOrder($order) {
		$response = $this->requery($order->id, $order->getCalculatedTotalAmount());
		
		if ($response->isRedirect()) {
			// redirect to offsite payment gateway
			$response->redirect();
		} elseif ($response->isSuccessful()) {
			Console::output('Payment: Successful');
			$order->detachBehavior(\yii\behaviors\BlameableBehavior::className());
			$order->detachBehavior(\common\behaviors\IpBehavior::className());
			
			if (!isset($order->invoice)) {
				
				$created = $order->billTo(isset($order->created_by) ? $order->created_by : 0);
				if ($created === false)  {
					$order->trigger(Order::EVENT_ORDER_FAILED, new \yii\base\Event(['sender' => $order]) );
					throw new \Exception('Invoice failed to be created');
				}
				Console::output('Update: Invoice created');
			}
			
			if (!$order->invoice->isPaid) {
				$order->invoice->pay($order->getCalculatedTotalAmount(), Invoice::STATUS_PAID_MANUALLY);
				
				if ($order->invoice->save() && $order->invoice->isPaid) {
					$order->paymentSuccessCallBack($order->invoice);
					
					Console::output('Update: Order updated');
				} else {
					$order->trigger(Order::EVENT_ORDER_FAILED, new \yii\base\Event(['sender' => $order]) );
					throw new \Exception(Html::errorSummary($order->invoice));
				}
			}
			//return $this->onPaymentSuccessful($gateway, $invoice, $payableCallBack);
		} else {
			// payment failed: display message to customer
			$order->trigger(Order::EVENT_ORDER_FAILED, new \yii\base\Event(['sender' => $order]) );
			Console::output($response->getMessage());
			//return $this->onPaymentError($gateway, $response, $invoice);
		}
	}
}