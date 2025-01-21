<?php

namespace Tests\Omnipay\UnitPay;

use Omnipay\UnitPay\Gateway;
use PHPUnit\Framework\TestCase;

class TestModeTest extends TestCase
{
    protected $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new Gateway();
        $this->gateway->setSecretKey('live-secret-key');
        $this->gateway->setTestSecretKey('test-secret-key');
        $this->gateway->setPublicKey('public-key');
    }

    public function testLiveMode()
    {
        $this->gateway->setTestMode(false);

        $request = $this->gateway->purchase([
            'amount' => '10.00',
            'currency' => 'RUB',
            'transactionId' => '12345',
            'description' => 'Test Purchase'
        ]);

        $data = $request->getData();

        $this->assertArrayNotHasKey('test', $data);

        $params = [
            $data['account'],
            $data['currency'],
            $data['desc'],
            $data['sum'],
            'live-secret-key'
        ];
        $expectedSignature = hash('sha256', implode('{up}', $params));
        $this->assertSame($expectedSignature, $data['signature']);
    }

    public function testTestMode()
    {
        $this->gateway->setTestMode(true);

        $request = $this->gateway->purchase([
            'amount' => '10.00',
            'currency' => 'RUB',
            'transactionId' => '12345',
            'description' => 'Test Purchase'
        ]);

        $data = $request->getData();

        $this->assertArrayHasKey('test', $data);
        $this->assertEquals(1, $data['test']);

        $params = [
            $data['account'],
            $data['currency'],
            $data['desc'],
            $data['sum'],
            'live-secret-key'
        ];
        $expectedSignature = hash('sha256', implode('{up}', $params));
        $this->assertSame($expectedSignature, $data['signature']);
    }
}