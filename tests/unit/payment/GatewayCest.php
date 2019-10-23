<?php
use ant\payment\components\PayPalExpressGateway;

class GatewayCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function tryToTest(UnitTester $I)
    {
    	// Error response
    	$response = 'TOKEN=EC%2d9PU06830A46581917&SUCCESSPAGEREDIRECTREQUESTED=false&TIMESTAMP=2016%2d06%2d28T15%3a30%3a29Z&CORRELATIONID=153b04f531584&ACK=Failure&VERSION=119%2e0&BUILD=000000&L_ERRORCODE0=10001&L_SHORTMESSAGE0=Internal%20Error&L_LONGMESSAGE0=Internal%20Error&L_SEVERITYCODE0=Error';
    	$paymentMethod = $this->getPaymentWithResponse($I, $response);
		
    	$I->assertFalse($paymentMethod->isPaymentValid());
    	$I->assertEquals(0, $paymentMethod->paymentRecord->is_valid);
    	$I->assertEquals('Internal Error', $paymentMethod->paymentRecord->error);
    	$I->assertEquals('Internal Error', $paymentMethod->paymentRecord->error);
    }
    
    public function testSuccessResponse(UnitTester $I) {
    	// Success response
    	$response = 'TOKEN=EC%2d96V667110D1727015&SUCCESSPAGEREDIRECTREQUESTED=true&TIMESTAMP=2013%2d02%2d20T13%3a54%3a28Z&CORRELATIONID=f78b888897f8a&ACK=Success&VERSION=85%2e0&BUILD=5060305&INSURANCEOPTIONSELECTED=false&SHIPPINGOPTIONISDEFAULT=false&PAYMENTINFO_0_TRANSACTIONID=8RM57414KW761861W&PAYMENTINFO_0_RECEIPTID=0368%2d2088%2d8643%2d7560&PAYMENTINFO_0_TRANSACTIONTYPE=expresscheckout&PAYMENTINFO_0_PAYMENTTYPE=instant&PAYMENTINFO_0_ORDERTIME=2013%2d02%2d20T13%3a54%3a03Z&PAYMENTINFO_0_AMT=10%2e00&PAYMENTINFO_0_FEEAMT=0%2e59&PAYMENTINFO_0_TAXAMT=0%2e00&PAYMENTINFO_0_CURRENCYCODE=USD&PAYMENTINFO_0_PAYMENTSTATUS=Completed&PAYMENTINFO_0_PENDINGREASON=None&PAYMENTINFO_0_REASONCODE=None&PAYMENTINFO_0_PROTECTIONELIGIBILITY=Ineligible&PAYMENTINFO_0_PROTECTIONELIGIBILITYTYPE=None&PAYMENTINFO_0_SECUREMERCHANTACCOUNTID=VZTRGMSKHHAEW&PAYMENTINFO_0_ERRORCODE=0&PAYMENTINFO_0_ACK=Success';
    	$paymentMethod = $this->getPaymentWithResponse($I, $response);
    	
    	$I->assertTrue($paymentMethod->isPaymentValid());
    	$I->assertEquals(1, $paymentMethod->paymentRecord->is_valid);
    	$I->assertEquals(null, $paymentMethod->paymentRecord->error);
    }
    
    protected function getPaymentWithResponse($I, $responseString) {
    	$paymentMethod = new PayPalExpressGateway;
    	$gateway = $I->getProperty($paymentMethod, '_gateway');
    	$request = $I->invokeMethod($gateway, 'completePurchase', array());
    	$response = $I->invokeMethod($request, 'createResponse', array($responseString));
    	$I->setProperty($paymentMethod, '_response', $response);
   
    	return $paymentMethod;
    }
}