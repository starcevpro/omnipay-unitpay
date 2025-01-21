<?php

namespace Omnipay\UnitPay\Message;

class CompletePurchaseRequest extends AbstractUnitPayRequest
{
    public function getData(): array
    {
        return $this->httpRequest->request->all();
    }

    public function sendData($data): UnitPayResponseInterface
    {
        return new CompletePurchaseResponse($this, $data);
    }
}