<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Entity\TransactionLogInterface;
use DateTimeInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Exception\UnsupportedMethodException;

final class TransactionLogFactory implements TransactionLogFactoryInterface
{
    /**
     * @param class-string $transactionLogClassName
     */
    public function __construct(
        private string $transactionLogClassName,
    ) {
    }

    /**
     * @throws UnsupportedMethodException
     */
    public function createNew(): object
    {
        throw new UnsupportedMethodException('createNew');
    }

    public function create(
        DateTimeInterface $occurredAt,
        PaymentInterface $payment,
        string $description,
        array $context = [],
        string $type = TransactionLogInterface::TYPE_DEBUG,
    ): TransactionLogInterface {
        /** @var TransactionLogInterface $transactionLog */
        $transactionLog = new $this->transactionLogClassName(
            $occurredAt,
            $payment,
            $description,
            $context,
            $type,
        );

        return $transactionLog;
    }
}
