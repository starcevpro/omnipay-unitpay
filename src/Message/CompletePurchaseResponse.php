<?php

namespace Omnipay\UnitPay\Message;

use Omnipay\Common\Message\AbstractResponse;

class CompletePurchaseResponse extends AbstractResponse implements UnitPayResponseInterface
{
    public function isSuccessful(): bool
    {
        if (!isset($this->data['method']) || !isset($this->data['params'])) {
            return false;
        }

        if ($this->data['method'] !== 'pay') {
            return false;
        }

        $params = $this->data['params'];
        $signature = $params['signature'] ?? null;
        unset($params['signature']);

        ksort($params);
        $params[] = $this->request->getSecretKey();

        $localSignature = hash('sha256', implode('{up}', $params));

        return hash_equals($localSignature, $signature);
    }

    public function getTransactionReference()
    {
        return $this->data['params']['unitpayId'] ?? null;
    }

    public function getTransactionId()
    {
        return $this->data['params']['account'] ?? null;
    }

    public function getAmount()
    {
        return $this->data['params']['orderSum'] ?? null;
    }

    public function getCurrency()
    {
        return $this->data['params']['orderCurrency'] ?? null;
    }
}