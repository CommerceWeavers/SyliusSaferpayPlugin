<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Event;

final class SaferpayPaymentEvent
{
    private \DateTimeInterface $createdAt;

    public function __construct(
        private int $paymentId,
        private string $status,
        private string $description,
        private array $context,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getPaymentId(): int
    {
        return $this->paymentId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
