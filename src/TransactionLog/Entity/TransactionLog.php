<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Entity;

use DateTimeInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\Uid\Uuid;

class TransactionLog implements TransactionLogInterface
{
    private Uuid $id;

    public function __construct(
        private DateTimeInterface $occurredAt,
        private PaymentInterface $payment,
        private string $description,
        private array $context,
        private string $type,
    ) {
        $this->id = Uuid::v4();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getOccurredAt(): DateTimeInterface
    {
        return $this->occurredAt;
    }

    public function getPayment(): PaymentInterface
    {
        return $this->payment;
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
