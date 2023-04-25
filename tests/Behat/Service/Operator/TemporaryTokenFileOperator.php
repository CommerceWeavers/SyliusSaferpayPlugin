<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Operator;

final class TemporaryTokenFileOperator implements TemporaryTokenOperatorInterface
{
    private string $temporaryFilePath;

    public function __construct(string $projectDirectory)
    {
        $this->temporaryFilePath = $projectDirectory . '/var/temporaryToken.txt';
    }

    public function getToken(): string
    {
        if ($this->hasToken()) {
            /** @var string $token */
            $token = file_get_contents($this->temporaryFilePath);

            return $token;
        }

        throw new \Exception('There is no temporary token set.');
    }

    public function setToken(string $token): void
    {
        file_put_contents($this->temporaryFilePath, $token);
    }

    public function hasToken(): bool
    {
        return file_exists($this->temporaryFilePath);
    }

    public function clearToken(): void
    {
        if (file_exists($this->temporaryFilePath)) {
            unlink($this->temporaryFilePath);
        }
    }
}
