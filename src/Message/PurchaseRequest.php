<?php

namespace Omnipay\UnitPay\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

class PurchaseRequest extends AbstractUnitPayRequest
{
    protected string $endpoint = 'https://unitpay.ru/pay/';

    public function getEmail()
    {
        return $this->getParameter('email');
    }

    public function setEmail($value): PurchaseRequest
    {
        return $this->setParameter('email', $value);
    }

    /**
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate(
            'amount',
            'currency',
            'transactionId',
            'description'
        );

        $data = [
            'sum' => $this->getAmount(),
            'account' => $this->getTransactionId(),
            'desc' => $this->getDescription(),
            'currency' => $this->getCurrency()
        ];

        if ($this->getTestMode()) {
            $data['test'] = 1;
        }

        $email = $this->getEmail();
        if (!empty($email)) {
            $data['customerEmail'] = $email;
        }

        $data['signature'] = $this->generateSignature($data);

        return $data;
    }

    protected function generateSignature($data): string
    {
        // Всегда используем боевой ключ для подписи
        $params = [
            $data['account'],
            $data['currency'],
            $data['desc'],
            $data['sum'],
            $this->getSecretKey()
        ];

        return hash('sha256', implode('{up}', $params));
    }

    public function sendData($data): UnitPayResponseInterface
    {
        $url = $this->endpoint . $this->getPublicKey();
        $query = http_build_query($data);

        return new PurchaseResponse($this, $data, $url . '?' . $query);
    }

    public function getSecretKey()
    {
        return $this->getParameter('secretKey');
    }

    public function setSecretKey($value): PurchaseRequest
    {
        return $this->setParameter('secretKey', $value);
    }

    public function getTestSecretKey()
    {
        return $this->getParameter('testSecretKey');
    }

    public function setTestSecretKey($value): PurchaseRequest
    {
        return $this->setParameter('testSecretKey', $value);
    }

    public function getPublicKey()
    {
        return $this->getParameter('publicKey');
    }

    public function setPublicKey($value): PurchaseRequest
    {
        return $this->setParameter('publicKey', $value);
    }
}