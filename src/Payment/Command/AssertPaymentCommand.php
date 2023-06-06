<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payment\Command;

final class AssertPaymentCommand
{
    public function __construct(private string $payumToken)
    {
    }

    public function getPayumToken(): string
    {
        return $this->payumToken;
    }
}
