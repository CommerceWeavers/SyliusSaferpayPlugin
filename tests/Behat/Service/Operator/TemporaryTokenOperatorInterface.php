<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Operator;

interface TemporaryTokenOperatorInterface
{
    public function getToken(): string;

    public function setToken(string $token): void;

    public function hasToken(): bool;

    public function clearToken(): void;
}
