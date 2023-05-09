<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;

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
            'MaskedNumber' => $this->maskedNumber,
            'ExpYear' => $this->expirationYear,
            'ExpMonth' => $this->expirationMonth,
            'HolderName' => $this->holderName,
            'CountryCode' => $this->countryCode,
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
