<?php

namespace Tests\Omnipay\UnitPay;

use Omnipay\UnitPay\Message\CompletePurchaseRequest;
use Omnipay\UnitPay\Message\CompletePurchaseResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class CompletePurchaseRequestTest extends TestCase
{
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();

        $httpRequest = new Request();
        $this->request = new CompletePurchaseRequest($this->getHttpClient(), $httpRequest);
        $this->request->initialize([
            'secretKey' => 'test-secret-key'
        ]);
    }

    protected function getHttpClient()
    {
        return $this->getMockBuilder('Omnipay\Common\Http\ClientInterface')->getMock();
    }

    public function testValidCallback()
    {
        $params = [
            'method' => 'pay',
            'params' => [
                'account' => '12345',
                'orderSum' => '10.00',
                'orderCurrency' => 'RUB',
                'unitpayId' => 'test-payment-id'
            ]
        ];

        // Генерируем подпись
        $signatureParams = $params['params'];
        ksort($signatureParams);
        $signatureParams[] = 'test-secret-key';
        $params['params']['signature'] = hash('sha256', implode('{up}', $signatureParams));

        $response = $this->request->sendData($params);

        $this->assertInstanceOf(CompletePurchaseResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('test-payment-id', $response->getTransactionReference());
        $this->assertSame('12345', $response->getTransactionId());
        $this->assertSame('10.00', $response->getAmount());
        $this->assertSame('RUB', $response->getCurrency());
    }

    public function testInvalidSignature()
    {
        $params = [
            'method' => 'pay',
            'params' => [
                'account' => '12345',
                'orderSum' => '10.00',
                'orderCurrency' => 'RUB',
                'unitpayId' => 'test-payment-id',
                'signature' => 'invalid-signature'
            ]
        ];

        $response = $this->request->sendData($params);

        $this->assertInstanceOf(CompletePurchaseResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
    }

    public function testInvalidMethod()
    {
        $params = [
            'method' => 'error',
            'params' => [
                'account' => '12345',
                'orderSum' => '10.00'
            ]
        ];

        $response = $this->request->sendData($params);

        $this->assertInstanceOf(CompletePurchaseResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
    }
}