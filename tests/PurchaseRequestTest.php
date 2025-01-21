<?php

namespace Tests\Omnipay\UnitPay;

use Omnipay\UnitPay\Message\PurchaseRequest;
use PHPUnit\Framework\TestCase;

class PurchaseRequestTest extends TestCase
{
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize([
            'amount' => '10.00',
            'currency' => 'RUB',
            'transactionId' => '12345',
            'description' => 'Test Purchase',
            'secretKey' => 'test-secret-key',
            'publicKey' => 'test-public-key'
        ]);
    }

    protected function getHttpClient()
    {
        return $this->getMockBuilder('Omnipay\Common\Http\ClientInterface')->getMock();
    }

    protected function getHttpRequest()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetDataWithoutEmail()
    {
        $data = $this->request->getData();

        $this->assertSame('10.00', $data['sum']);
        $this->assertSame('12345', $data['account']);
        $this->assertSame('Test Purchase', $data['desc']);
        $this->assertSame('RUB', $data['currency']);
        $this->assertArrayNotHasKey('customerEmail', $data);
        $this->assertArrayHasKey('signature', $data);
    }

    public function testGetDataWithEmail()
    {
        $this->request->setEmail('test@example.com');
        $data = $this->request->getData();

        $this->assertSame('10.00', $data['sum']);
        $this->assertSame('12345', $data['account']);
        $this->assertSame('Test Purchase', $data['desc']);
        $this->assertSame('RUB', $data['currency']);
        $this->assertSame('test@example.com', $data['customerEmail']);
        $this->assertArrayHasKey('signature', $data);
    }

    public function testGetDataWithEmptyEmail()
    {
        $this->request->setEmail('');
        $data = $this->request->getData();

        $this->assertArrayNotHasKey('customerEmail', $data);
    }

    public function testGenerateSignature()
    {
        $data = $this->request->getData();

        $params = [
            $data['account'],
            $data['currency'],
            $data['desc'],
            $data['sum'],
            'test-secret-key'
        ];
        $expectedSignature = hash('sha256', implode('{up}', $params));

        $this->assertSame($expectedSignature, $data['signature']);
    }

    public function testSendData()
    {
        $data = $this->request->getData();
        $response = $this->request->sendData($data);

        $this->assertInstanceOf('Omnipay\UnitPay\Message\PurchaseResponse', $response);
        $this->assertStringContainsString('test-public-key', $response->getRedirectUrl());
    }

    public function testEmailAccessors()
    {
        $email = 'test@example.com';
        $this->request->setEmail($email);

        $this->assertSame($email, $this->request->getEmail());
    }
}