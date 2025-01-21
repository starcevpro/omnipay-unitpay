<?php

namespace Omnipay\UnitPay\Message;

use Omnipay\Common\Message\AbstractRequest;

abstract class AbstractUnitPayRequest extends AbstractRequest
{
    abstract public function sendData($data): UnitPayResponseInterface;

    public function getSecretKey()
    {
        return $this->getParameter('secretKey');
    }

    public function setSecretKey($value): AbstractUnitPayRequest
    {
        return $this->setParameter('secretKey', $value);
    }

    public function getTestSecretKey()
    {
        return $this->getParameter('testSecretKey');
    }

    public function setTestSecretKey($value): AbstractUnitPayRequest
    {
        return $this->setParameter('testSecretKey', $value);
    }

    public function getPublicKey()
    {
        return $this->getParameter('publicKey');
    }

    public function setPublicKey($value): AbstractUnitPayRequest
    {
        return $this->setParameter('publicKey', $value);
    }
}