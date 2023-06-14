<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Error;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\PaymentMeans;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Transaction;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;

class RefundResponse implements ResponseInterface
{
    private function __construct(
        private int $statusCode,
        private ResponseHeader $responseHeader,
        private ?Transaction $transaction,
        private ?PaymentMeans $paymentMeans,
        private ?Error $error,
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

    public function getError(): ?Error
    {
        return $this->error;
    }

    public function isSuccessful(): bool
    {
        return 200 <= $this->statusCode && $this->statusCode <= 299;
    }

    public function toArray(): array
    {
        return [
            'StatusCode' => $this->getStatusCode(),
            'ResponseHeader' => $this->getResponseHeader()->toArray(),
            'Transaction' => $this->getTransaction()?->toArray(),
            'PaymentMeans' => $this->getPaymentMeans()?->toArray(),
            'Error' => $this->getError()?->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['StatusCode'] ?? 400,
            ResponseHeader::fromArray($data['ResponseHeader']),
            isset($data['Transaction']) ? Transaction::fromArray($data['Transaction']) : null,
            isset($data['PaymentMeans']) ? PaymentMeans::fromArray($data['PaymentMeans']) : null,
            isset($data['ErrorName']) ? Error::fromArray($data) : null,
        );
    }
}
