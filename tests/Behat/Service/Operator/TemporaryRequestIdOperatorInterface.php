<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Operator;

interface TemporaryRequestIdOperatorInterface
{
    public function getRequestId(): string;

    public function setRequestId(string $requestId): void;

    public function hasRequestId(): bool;

    public function clearRequestId(): void;
}
