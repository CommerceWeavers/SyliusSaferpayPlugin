<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\PaymentMeans;

class Card
{
    private function __construct(
        private string $maskedNumber,
        private int $expirationYear,
        private int $expirationMonth,
        private string $holderName,
        private string $countryCode,
    ) {
    }

    public function getMaskedNumber(): string
    {
        return $this->maskedNumber;
    }

    public function getExpirationYear(): int
    {
        return $this->expirationYear;
    }

    public function getExpirationMonth(): int
    {
        return $this->expirationMonth;
    }

    public function getHolderName(): string
    {
        return $this->holderName;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function toArray(): array
    {
        return [
            'MaskedNumber' => $this->getMaskedNumber(),
            'ExpYear' => $this->getExpirationYear(),
            'ExpMonth' => $this->getExpirationMonth(),
            'HolderName' => $this->getHolderName(),
            'CountryCode' => $this->getCountryCode(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['MaskedNumber'],
            $data['ExpYear'],
            $data['ExpMonth'],
            $data['HolderName'],
            $data['CountryCode'],
        );
    }
}
