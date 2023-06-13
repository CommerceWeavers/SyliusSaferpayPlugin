<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\PaymentMeans;

class Brand
{
    private function __construct(
        private string $name,
        private ?string $paymentMethod,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function toArray(): array
    {
        return [
            'Name' => $this->getName(),
            'PaymentMethod' => $this->getPaymentMethod(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['Name'],
            $data['PaymentMethod'] ?? null,
        );
    }
}
