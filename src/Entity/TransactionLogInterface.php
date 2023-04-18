<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Entity;

use DateTimeInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface TransactionLogInterface extends ResourceInterface
{
    public function getCreatedAt(): ?DateTimeInterface;

    public function setCreatedAt(DateTimeInterface $createdAt): void;

    public function getPayment(): ?PaymentInterface;

    public function setPayment(?PaymentInterface $payment): void;

    public function getStatus(): ?string;

    public function setStatus(?string $status): void;

    public function getDescription(): ?string;

    public function setDescription(?string $description): void;

    public function getContext(): array;

    public function setContext(array $context): void;
}
