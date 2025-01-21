<?php

namespace Omnipay\UnitPay;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\RequestInterface;

class Gateway extends AbstractGateway
{
    public function getName(): string
    {
        return 'UnitPay';
    }

    public function getDefaultParameters(): array
    {
        return [
            'secretKey' => '',
            'testSecretKey' => '',
            'publicKey' => '',
            'currency' => 'RUB',
            'testMode' => false
        ];
    }

    public function getSecretKey(): string
    {
        return $this->getParameter('secretKey');
    }

    public function setSecretKey($value): self
    {
        return $this->setParameter('secretKey', $value);
    }

    public function getTestSecretKey(): string
    {
        return $this->getParameter('testSecretKey');
    }

    public function setTestSecretKey($value): self
    {
        return $this->setParameter('testSecretKey', $value);
    }

    public function getPublicKey(): string
    {
        return $this->getParameter('publicKey');
    }

    public function setPublicKey($value): self
    {
        return $this->setParameter('publicKey', $value);
    }

    public function setTestMode($value): self
    {
        return $this->setParameter('testMode', $value);
    }

    public function getSignatureSecretKey(): string
    {
        return $this->getSecretKey();
    }

    public function getOperationalSecretKey(): string
    {
        return $this->getTestMode() ? $this->getTestSecretKey() : $this->getSecretKey();
    }

    public function purchase(array $options = array()): RequestInterface|AbstractRequest
    {
        return $this->createRequest(\Omnipay\UnitPay\Message\PurchaseRequest::class, $options);
    }

    public function completePurchase(array $options = array()): RequestInterface|AbstractRequest
    {
        return $this->createRequest(\Omnipay\UnitPay\Message\CompletePurchaseRequest::class, $options);
    }
}