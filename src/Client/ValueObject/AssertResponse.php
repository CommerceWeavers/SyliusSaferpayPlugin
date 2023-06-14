<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Liability;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Payer;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\PaymentMeans;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Transaction;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;

class AssertResponse implements ResponseInterface
{
    private function __construct(
        private int $statusCode,
        private ResponseHeader $responseHeader,
        private ?Transaction $transaction,
        private ?PaymentMeans $paymentMeans,
        private ?Payer $payer,
        private ?Liability $liability,
    ) {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseHeader(): ResponseHeader
    {
        return $this->responseHeader;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function getPaymentMeans(): ?PaymentMeans
    {
        return $this->paymentMeans;
    }

    public function getPayer(): ?Payer
    {
        return $this->payer;
    }

    public function getLiability(): ?Liability
    {
        return $this->liability;
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function toArray(): array
    {
        return [
            'StatusCode' => $this->getStatusCode(),
            'ResponseHeader' => $this->getResponseHeader()->toArray(),
            'Transaction' => $this->getTransaction()?->toArray(),
            'PaymentMeans' => $this->getPaymentMeans()?->toArray(),
            'Payer' => $this->getPayer()?->toArray(),
            'Liability' => $this->getLiability()?->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['StatusCode'] ?? 400,
            ResponseHeader::fromArray($data['ResponseHeader']),
            isset($data['Transaction']) ? Transaction::fromArray($data['Transaction']) : null,
            isset($data['PaymentMeans']) ? PaymentMeans::fromArray($data['PaymentMeans']) : null,
            isset($data['Payer']) ? Payer::fromArray($data['Payer']) : null,
            isset($data['Liability']) ? Liability::fromArray($data['Liability']) : null,
        );
    }
}
