<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse\Error;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;

class AuthorizeResponse
{
    private function __construct(
        private int $statusCode,
        private ResponseHeader $responseHeader,
        private ?string $token,
        private ?string $expiration,
        private ?string $redirectUrl,
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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getExpiration(): ?string
    {
        return $this->expiration;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
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
            'StatusCode' => $this->statusCode,
            'ResponseHeader' => $this->responseHeader->toArray(),
            'Token' => $this->token,
            'Expiration' => $this->expiration,
            'RedirectUrl' => $this->redirectUrl,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['StatusCode'],
            ResponseHeader::fromArray($data['ResponseHeader']),
            $data['Token'] ?? null,
            $data['Expiration'] ?? null,
            $data['RedirectUrl'] ?? null,
            isset($data['ErrorName']) ? Error::fromArray($data) : null,
        );
    }
}
