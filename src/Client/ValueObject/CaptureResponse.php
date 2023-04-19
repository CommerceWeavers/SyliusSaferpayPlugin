<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;

class CaptureResponse
{
    public function __construct(
        private ResponseHeader $responseHeader,
        private string $captureId,
        private string $status,
        private string $date,
    ) {
    }

    public function getResponseHeader(): ResponseHeader
    {
        return $this->responseHeader;
    }

    public function getCaptureId(): string
    {
        return $this->captureId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ResponseHeader::fromArray($data['ResponseHeader']),
            $data['CaptureId'],
            $data['Status'],
            $data['Date'],
        );
    }
}
