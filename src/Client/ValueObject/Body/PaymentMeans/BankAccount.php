<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\PaymentMeans;

class BankAccount
{
    private function __construct(
        private string $iban,
        private ?string $holderName,
        private ?string $bankName,
        private ?string $countryCode,
    ) {
    }

    public function getIban(): string
    {
        return $this->iban;
    }

    public function getHolderName(): ?string
    {
        return $this->holderName;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function toArray(): array
    {
        return [
            'IBAN' => $this->getIban(),
            'HolderName' => $this->getHolderName(),
            'BankName' => $this->getBankName(),
            'CountryCode' => $this->getCountryCode(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['IBAN'],
            $data['HolderName'] ?? null,
            $data['BankName'] ?? null,
            $data['CountryCode'] ?? null,
        );
    }
}
