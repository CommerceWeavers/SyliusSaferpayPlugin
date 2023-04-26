<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Event;

final class SaferpayPaymentEvent
{
    public const TYPE_SUCCESS = 'success';

    public const TYPE_ERROR = 'error';

    public function __construct(
        private \DateTimeInterface $occurredAt,
        private int $paymentId,
        private string $description,
        private array $context,
        private string $type,
    ) {
    }

    public function getOccurredAt(): \DateTimeInterface
    {
        return $this->occurredAt;
    }

    public function getPaymentId(): int
    {
        return $this->paymentId;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
