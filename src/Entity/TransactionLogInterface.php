<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Entity;

use DateTimeInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface TransactionLogInterface extends ResourceInterface
{
    public const TYPE_SUCCESS = 'success';

    public function getOccurredAt(): ?DateTimeInterface;

    public function getPayment(): ?PaymentInterface;

    public function getDescription(): ?string;

    public function getContext(): array;

    public function getType(): string;
}
