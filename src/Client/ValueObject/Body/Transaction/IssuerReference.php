<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Transaction;

class IssuerReference
{
    private function __construct(private string $transactionStamp)
    {
    }

    public function getTransactionStamp(): string
    {
        return $this->transactionStamp;
    }

    public static function fromArray(array $data): self
    {
        return new self($data['TransactionStamp']);
    }
}
