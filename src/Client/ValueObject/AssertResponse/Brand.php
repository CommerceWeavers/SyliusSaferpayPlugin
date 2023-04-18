<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;

class Brand
{
    public function __construct(
        private string $paymentMethod,
        private string $name,
    ) {
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['PaymentMethod'],
            $data['Name'],
        );
    }
}
