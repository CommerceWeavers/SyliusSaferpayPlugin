<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payment\Command;

final class CapturePaymentCommand
{
    public function __construct(private string $payumToken)
    {
    }

    public function getPayumToken(): string
    {
        return $this->payumToken;
    }
}
