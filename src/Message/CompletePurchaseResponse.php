<?php

namespace Omnipay\UnitPay\Message;

use Omnipay\Common\Message\AbstractResponse;

class CompletePurchaseResponse extends AbstractResponse implements UnitPayResponseInterface
{
    protected array $allowedIps = [
        '31.186.100.49',
        '178.132.203.105',
        '52.29.152.23',
        '52.19.56.234',
    ];

    public function isSuccessful(): bool
    {
        if (!$this->isAllowedIp()) {
            return $this->error('Неверный IP-адрес');
        }

        if (!isset($this->data['method']) || !isset($this->data['params'])) {
            return $this->error('Неверный формат запроса');
        }

        $method = $this->data['method'];
        $params = $this->data['params'];

        if (!$this->verifySignature($method, $params)) {
            return $this->error('Неверная подпись');
        }

        switch ($method) {
            case 'check':
                return $this->success('Запрос успешно обработан');
            case 'pay':
                return $this->success('Платеж успешно обработан');
            case 'preauth':
                if (!isset($params['isPreauth']) || $params['isPreauth'] != 1) {
                    return $this->error('Неверный запрос преавторизации');
                }
                return $this->success('Преавторизация успешно обработана');
            case 'error':
                $errorMessage = $params['errorMessage'] ?? 'Неизвестная ошибка';
                return $this->success('Ошибка обработана: ' . $errorMessage);
            default:
                return $this->error('Неверный метод');
        }
    }

    public function setAllowedIps(array $ips): void
    {
        $this->allowedIps = $ips;
    }

    protected function isAllowedIp(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        return in_array($ip, $this->allowedIps);
    }

    protected function verifySignature(string $method, array $params): bool
    {
        if (empty($params['signature'])) {
            return false;
        }

        $signatureParams = $params;
        unset($signatureParams['signature']);
        ksort($signatureParams);

        $signString = $method . '{up}' . implode('{up}', $signatureParams) . '{up}' . $this->request->getSecretKey();
        return hash_equals(
            hash('sha256', $signString),
            $params['signature']
        );
    }

    protected function success(string $message): bool
    {
        $this->data['response'] = [
            'result' => ['message' => $message]
        ];
        return true;
    }

    protected function error(string $message): bool
    {
        $this->data['response'] = [
            'error' => [
                'message' => $message,
                'locale' => $this->data['params']['locale'] ?? 'ru'
            ]
        ];
        return false;
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

    public function getMessage(): ?string
    {
        if (isset($this->data['response']['result'])) {
            return $this->data['response']['result']['message'];
        }
        if (isset($this->data['response']['error'])) {
            return $this->data['response']['error']['message'];
        }
        return null;
    }
}
