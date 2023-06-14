<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Error;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;

class AuthorizeResponse implements ResponseInterface
{
    private function __construct(
        private int $statusCode,
        private ResponseHeader $responseHeader,
        private ?string $token,
        private ?string $expiration,
        private ?string $redirectUrl,
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

    public function isSuccessful(): bool
    {
        return 200 <= $this->statusCode && $this->statusCode <= 299;
    }

    public function toArray(): array
    {
        return [
            'StatusCode' => $this->getStatusCode(),
            'ResponseHeader' => $this->getResponseHeader()->toArray(),
            'Token' => $this->getToken(),
            'Expiration' => $this->getExpiration(),
            'RedirectUrl' => $this->getRedirectUrl(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['StatusCode'] ?? 400,
            ResponseHeader::fromArray($data['ResponseHeader']),
            $data['Token'] ?? null,
            $data['Expiration'] ?? null,
            $data['RedirectUrl'] ?? null,
        );
    }
}
