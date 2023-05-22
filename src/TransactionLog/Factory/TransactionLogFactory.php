<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Entity\TransactionLogInterface;
use DateTimeInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Exception\UnsupportedMethodException;

final class TransactionLogFactory implements TransactionLogFactoryInterface
{
    /**
     * @param class-string $transactionLogClassName
     */
    public function __construct(private string $transactionLogClassName)
    {
    }

    /**
     * @throws UnsupportedMethodException
     */
    public function createNew(): object
    {
        throw new UnsupportedMethodException('createNew');
    }

    public function createInformationalLog(
        DateTimeInterface $occurredAt,
        PaymentInterface $payment,
        string $description,
        array $context,
    ): TransactionLogInterface {
        /** @var TransactionLogInterface $transactionLog */
        $transactionLog = new $this->transactionLogClassName(
            $occurredAt,
            $payment,
            $description,
            $context,
            TransactionLogInterface::TYPE_INFO,
        );

        return $transactionLog;
    }

    public function createErrorLog(
        DateTimeInterface $occurredAt,
        PaymentInterface $payment,
        string $description,
        array $context,
    ): TransactionLogInterface {
        /** @var TransactionLogInterface $transactionLog */
        $transactionLog = new $this->transactionLogClassName(
            $occurredAt,
            $payment,
            $description,
            $context,
            TransactionLogInterface::TYPE_ERROR,
        );

        return $transactionLog;
    }
}
