<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Error;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;

class CaptureResponse implements ResponseInterface
{
    private function __construct(
        private int $statusCode,
        private ResponseHeader $responseHeader,
        private ?string $captureId,
        private ?string $status,
        private ?string $date,
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

    public function getCaptureId(): ?string
    {
        return $this->captureId;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getDate(): ?string
    {
        return $this->date;
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
            'CaptureId' => $this->getCaptureId(),
            'Status' => $this->getStatus(),
            'Date' => $this->getDate(),
            'Error' => $this->getError(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['StatusCode'] ?? 400,
            ResponseHeader::fromArray($data['ResponseHeader']),
            $data['CaptureId'] ?? null,
            $data['Status'] ?? null,
            $data['Date'] ?? null,
            isset($data['ErrorName']) ? Error::fromArray($data) : null,
        );
    }
}
