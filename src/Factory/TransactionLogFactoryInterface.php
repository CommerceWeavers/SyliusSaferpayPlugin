<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Entity\TransactionLogInterface;
use DateTimeInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface TransactionLogFactoryInterface extends FactoryInterface
{
    public function createInformationalLog(
        DateTimeInterface $occurredAt,
        PaymentInterface $payment,
        string $description,
        array $context,
    ): TransactionLogInterface;

    public function createErrorLog(
        DateTimeInterface $occurredAt,
        PaymentInterface $payment,
        string $description,
        array $context,
    ): TransactionLogInterface;
}
