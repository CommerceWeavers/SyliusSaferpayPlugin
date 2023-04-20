<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse\Liability;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse\PaymentMeans;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse\Transaction;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;

class AssertResponse
{
    private function __construct(
        private ResponseHeader $responseHeader,
        private Transaction $transaction,
        private PaymentMeans $paymentMeans,
        private Liability $liability,
    ) {
    }

    public function getResponseHeader(): ResponseHeader
    {
        return $this->responseHeader;
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    public function getPaymentMeans(): PaymentMeans
    {
        return $this->paymentMeans;
    }

    public function getLiability(): Liability
    {
        return $this->liability;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ResponseHeader::fromArray($data['ResponseHeader']),
            Transaction::fromArray($data['Transaction']),
            PaymentMeans::fromArray($data['PaymentMeans']),
            Liability::fromArray($data['Liability']),
        );
    }
}
