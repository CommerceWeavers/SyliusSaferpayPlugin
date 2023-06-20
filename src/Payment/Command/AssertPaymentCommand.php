<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payment\Command;

final class AssertPaymentCommand
{
    public function __construct(private string $payumToken, private string $orderToken)
    {
    }

    public function getPayumToken(): string
    {
        return $this->payumToken;
    }

    public function getOrderToken(): string
    {
        return $this->orderToken;
    }
}
