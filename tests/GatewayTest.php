<?php

namespace Tests\Omnipay\UnitPay;

use Omnipay\UnitPay\Gateway;
use PHPUnit\Framework\TestCase;

class GatewayTest extends TestCase
{
    /** @var Gateway */
    protected $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = new Gateway();
        $this->gateway->setSecretKey('test-secret-key');
        $this->gateway->setPublicKey('test-public-key');
    }

    public function testGatewayGetName()
    {
        $this->assertSame('UnitPay', $this->gateway->getName());
    }

    public function testGatewayGetDefaultParameters()
    {
        $defaults = $this->gateway->getDefaultParameters();

        $this->assertIsArray($defaults);
        $this->assertArrayHasKey('secretKey', $defaults);
        $this->assertArrayHasKey('publicKey', $defaults);
        $this->assertArrayHasKey('currency', $defaults);
    }

    public function testPurchase()
    {
        $request = $this->gateway->purchase([
            'amount' => '10.00',
            'currency' => 'RUB',
            'transactionId' => '12345',
            'description' => 'Test Purchase',
            'email' => 'test@example.com'
        ]);

        $this->assertInstanceOf('Omnipay\UnitPay\Message\PurchaseRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
        $this->assertSame('RUB', $request->getCurrency());
    }

    public function testCompletePurchase()
    {
        $request = $this->gateway->completePurchase([
            'amount' => '10.00',
            'currency' => 'RUB',
            'transactionId' => '12345'
        ]);

        $this->assertInstanceOf('Omnipay\UnitPay\Message\CompletePurchaseRequest', $request);
    }
}