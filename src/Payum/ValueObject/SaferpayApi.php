<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\ValueObject;

final class SaferpayApi
{
    public function __construct(
        private string $username,
        private string $password,
        private string $customerId,
        private string $terminalId,
        private bool $sandbox,
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getTerminalId(): string
    {
        return $this->terminalId;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }
}
