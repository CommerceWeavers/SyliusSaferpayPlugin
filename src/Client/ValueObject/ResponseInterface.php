<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;

interface ResponseInterface
{
    public function getStatusCode(): int;

    public function getResponseHeader(): ResponseHeader;

    public function isSuccessful(): bool;

    public function toArray(): array;
}
