<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Transaction;

class Amount
{
    private function __construct(
        private string $value,
        private string $currencyCode,
    ) {
    }

    public function getValue(): int
    {
        return (int) $this->value;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function toArray(): array
    {
        return [
            'Value' => $this->getValue(),
            'CurrencyCode' => $this->getCurrencyCode(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['Value'],
            $data['CurrencyCode'],
        );
    }
}
