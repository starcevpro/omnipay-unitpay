<?php

namespace Omnipay\UnitPay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface, UnitPayResponseInterface
{
    protected ?string $redirectUrl;

    public function __construct($request, $data, $redirectUrl)
    {
        parent::__construct($request, $data);
        $this->redirectUrl = $redirectUrl;
    }

    public function isSuccessful(): false
    {
        return false;
    }

    public function isRedirect(): true
    {
        return true;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function getRedirectMethod(): string
    {
        return 'GET';
    }

    public function getRedirectData(): null
    {
        return null;
    }
}