<?php

namespace Tests\Omnipay\UnitPay;

use Omnipay\Common\Exception\InvalidRequestException;
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
            'publicKey' => 'test-public-key',
            'email' => 'test@example.com'
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

    public function testGetDataWithEmail()
    {
        $data = $this->request->getData();

        $this->assertSame('10.00', $data['sum']);
        $this->assertSame('12345', $data['account']);
        $this->assertSame('Test Purchase', $data['desc']);
        $this->assertSame('RUB', $data['currency']);
        $this->assertSame('test@example.com', $data['customerEmail']);
        $this->assertArrayHasKey('signature', $data);
    }

    public function testGetDataWithPhone()
    {
        $this->request->setEmail(null);
        $this->request->setPhone('79991234567');
        $data = $this->request->getData();

        $this->assertSame('79991234567', $data['customerPhone']);
        $this->assertArrayNotHasKey('customerEmail', $data);
    }

    public function testGetDataWithCashItems()
    {
        $cashItems = [
            [
                'name' => 'Test Product',
                'count' => 1,
                'price' => 10.00,
                'currency' => 'RUB',
                'nds' => 'vat20',
                'type' => 'commodity',
                'paymentMethod' => 'full_payment'
            ]
        ];

        $this->request->setCashItems($cashItems);
        $data = $this->request->getData();

        $this->assertArrayHasKey('cashItems', $data);
        $decodedItems = json_decode(base64_decode($data['cashItems']), true);
        $this->assertEquals($cashItems, $decodedItems);
    }

    public function testGetDataWithLocale()
    {
        $this->request->setLocale('en');
        $data = $this->request->getData();

        $this->assertSame('en', $data['locale']);
    }

    public function testGetDataWithBackUrl()
    {
        $backUrl = 'https://example.com/back';
        $this->request->setBackUrl($backUrl);
        $data = $this->request->getData();

        $this->assertSame($backUrl, $data['backUrl']);
    }

    public function testGetDataWithSubscription()
    {
        $this->request->setSubscription('true');
        $data = $this->request->getData();

        $this->assertSame('true', $data['subscription']);
    }

    public function testValidationRequiresEmailOrPhone()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Either email or phone is required');

        $this->request->setEmail(null);
        $this->request->getData();
    }

    public function testGetDataWithBothEmailAndPhone()
    {
        $this->request->setPhone('79991234567');
        $data = $this->request->getData();

        $this->assertSame('test@example.com', $data['customerEmail']);
        $this->assertSame('79991234567', $data['customerPhone']);
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

    public function testLocaleAccessors()
    {
        $locale = 'en';
        $this->request->setLocale($locale);
        $this->assertSame($locale, $this->request->getLocale());
    }

    public function testBackUrlAccessors()
    {
        $backUrl = 'https://example.com/back';
        $this->request->setBackUrl($backUrl);
        $this->assertSame($backUrl, $this->request->getBackUrl());
    }

    public function testSubscriptionAccessors()
    {
        $subscription = 'true';
        $this->request->setSubscription($subscription);
        $this->assertSame($subscription, $this->request->getSubscription());
    }
}