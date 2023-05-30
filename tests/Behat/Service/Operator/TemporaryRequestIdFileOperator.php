<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Operator;

final class TemporaryRequestIdFileOperator implements TemporaryRequestIdOperatorInterface
{
    private string $temporaryFilePath;

    public function __construct(string $projectDirectory)
    {
        $this->temporaryFilePath = $projectDirectory . '/var/temporaryRequestId.txt';
    }

    public function getRequestId(): string
    {
        if ($this->hasRequestId()) {
            /** @var string $requestId */
            $requestId = file_get_contents($this->temporaryFilePath);

            return $requestId;
        }

        throw new \Exception('There is no temporary request id set.');
    }

    public function setRequestId(string $requestId): void
    {
        file_put_contents($this->temporaryFilePath, $requestId);
    }

    public function hasRequestId(): bool
    {
        return file_exists($this->temporaryFilePath);
    }

    public function clearRequestId(): void
    {
        if (file_exists($this->temporaryFilePath)) {
            unlink($this->temporaryFilePath);
        }
    }
}
