<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\PaymentMeans;

class PayPal
{
    private function __construct(
        private string $payerId,
        private string $email,
        private ?string $sellerProtectionStatus,
    ) {
    }

    public function getPayerId(): string
    {
        return $this->payerId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSellerProtectionStatus(): ?string
    {
        return $this->sellerProtectionStatus;
    }

    public function toArray(): array
    {
        return [
            'PayerId' => $this->getPayerId(),
            'Email' => $this->getEmail(),
            'SellerProtectionStatus' => $this->getSellerProtectionStatus(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['PayerId'],
            $data['Email'],
            $data['SellerProtectionStatus'] ?? null,
        );
    }
}
