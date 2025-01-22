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

    public function getPhone()
    {
        return $this->getParameter('phone');
    }

    public function setPhone($value): PurchaseRequest
    {
        return $this->setParameter('phone', $value);
    }

    public function getCashItems()
    {
        return $this->getParameter('cashItems');
    }

    public function setCashItems($value): PurchaseRequest
    {
        return $this->setParameter('cashItems', $value);
    }

    public function getLocale()
    {
        return $this->getParameter('locale');
    }

    public function setLocale($value): PurchaseRequest
    {
        return $this->setParameter('locale', $value);
    }

    public function getBackUrl()
    {
        return $this->getParameter('backUrl');
    }

    public function setBackUrl($value): PurchaseRequest
    {
        return $this->setParameter('backUrl', $value);
    }

    public function getSubscription()
    {
        return $this->getParameter('subscription');
    }

    public function setSubscription($value): PurchaseRequest
    {
        return $this->setParameter('subscription', $value);
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

        // Validate that either email or phone is present
        if (empty($this->getEmail()) && empty($this->getPhone())) {
            throw new InvalidRequestException("Either email or phone is required");
        }

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

        $phone = $this->getPhone();
        if (!empty($phone)) {
            $data['customerPhone'] = $phone;
        }

        $cashItems = $this->getCashItems();
        if (!empty($cashItems)) {
            $data['cashItems'] = base64_encode(json_encode($cashItems));
        }

        $locale = $this->getLocale();
        if (!empty($locale)) {
            $data['locale'] = $locale;
        }

        $backUrl = $this->getBackUrl();
        if (!empty($backUrl)) {
            $data['backUrl'] = $backUrl;
        }

        $subscription = $this->getSubscription();
        if (!empty($subscription)) {
            $data['subscription'] = $subscription;
        }

        $data['signature'] = $this->generateSignature($data);

        return $data;
    }

    protected function generateSignature($data): string
    {
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
