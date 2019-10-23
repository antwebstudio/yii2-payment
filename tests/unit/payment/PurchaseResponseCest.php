<?php

class PurchaseResponseCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function testGetRedirectResponse(UnitTester $I)
    {
		$client = new Guzzle\Http\Client;
		$request = new \Omnipay\IPay88\Message\PurchaseRequest($client, \Symfony\Component\HttpFoundation\Request::createFromGlobals());
		$response = new \Omnipay\IPay88\Message\PurchaseResponse($request, []);
		$response->getRedirectResponse();
    }
}