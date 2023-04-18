<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Entity;

use DateTimeInterface;
use Sylius\Component\Payment\Model\PaymentInterface;

class TransactionLog implements TransactionLogInterface
{
    private ?int $id = null;

    private ?DateTimeInterface $createdAt = null;

    private ?PaymentInterface $payment = null;

    private ?string $status = null;

    private ?string $description = null;

    private array $context = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getPayment(): ?PaymentInterface
    {
        return $this->payment;
    }

    public function setPayment(?PaymentInterface $payment): void
    {
        $this->payment = $payment;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }
}
