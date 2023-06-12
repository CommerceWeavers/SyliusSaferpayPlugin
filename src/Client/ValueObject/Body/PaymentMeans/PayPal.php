<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\PaymentMeans;

class PayPal
{
    private function __construct(
        private string $payerId,
        private string $sellerProtectionStatus,
        private string $email,
    ) {
    }

    public function getPayerId(): string
    {
        return $this->payerId;
    }

    public function getSellerProtectionStatus(): string
    {
        return $this->sellerProtectionStatus;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function toArray(): array
    {
        return [
            'PayerId' => $this->getPayerId(),
            'SellerProtectionStatus' => $this->getSellerProtectionStatus(),
            'Email' => $this->getEmail(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['PayerId'],
            $data['SellerProtectionStatus'],
            $data['Email'],
        );
    }
}
