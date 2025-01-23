<?php

namespace Omnipay\UnitPay\Message;

class CompletePurchaseRequest extends AbstractUnitPayRequest
{
    protected $allowedIps;

    public function setAllowedIps($value)
    {
        $this->allowedIps = $value;
        return $this;
    }

    public function getData(): array
    {
        $data = $this->httpRequest->request->all();

        if (empty($data)) {
            $data = $this->httpRequest->query->all();
        }

        if (!isset($data['method']) || !isset($data['params'])) {
            throw new \InvalidArgumentException('Invalid request format');
        }

        if ($this->allowedIps) {
            $data['allowedIps'] = $this->allowedIps;
        }

        if (is_array($data['params'])) {
            return [
                'method' => $data['method'],
                'params' => $data['params']
            ];
        }

        $params = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'params.')) {
                $paramKey = substr($key, 7);
                $params[$paramKey] = $value;
            }
        }

        return [
            'method' => $data['method'],
            'params' => $params
        ];
    }

    public function sendData($data): UnitPayResponseInterface
    {
        $response = new CompletePurchaseResponse($this, $data);
        if ($this->allowedIps) {
            $response->setAllowedIps($this->allowedIps);
        }
        return $response;
    }
}
