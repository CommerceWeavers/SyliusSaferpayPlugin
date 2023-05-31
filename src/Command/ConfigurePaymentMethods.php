<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Command;

class ConfigurePaymentMethods
{
    public function __construct(private string $paymentMethodId, private array $paymentMethods)
    {
    }

    public function getPaymentMethodId(): string
    {
        return $this->paymentMethodId;
    }

    public function getPaymentMethods(): array
    {
        return $this->paymentMethods;
    }
}
