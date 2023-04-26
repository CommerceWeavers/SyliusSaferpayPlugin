<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Event\Handler;

use CommerceWeavers\SyliusSaferpayPlugin\Event\Handler\Exception\PaymentNotFound;
use CommerceWeavers\SyliusSaferpayPlugin\Event\SaferpayPaymentEvent;
use CommerceWeavers\SyliusSaferpayPlugin\Factory\TransactionLogFactoryInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class SaferpayPaymentEventHandler
{
    public function __construct(
        private TransactionLogFactoryInterface $transactionLogFactory,
        private RepositoryInterface $transactionLogRepository,
        private PaymentRepositoryInterface $paymentRepository,
    ) {
    }

    /**
     * @throws PaymentNotFound
     */
    public function __invoke(SaferpayPaymentEvent $event): void
    {
        /** @var PaymentInterface|null $payment */
        $payment = $this->paymentRepository->find($event->getPaymentId());

        if (null === $payment) {
            throw new PaymentNotFound($event->getPaymentId());
        }

        $transactionLog = $this->transactionLogFactory->create(
            $event->getOccurredAt(),
            $payment,
            $event->getDescription(),
            $event->getContext(),
            $event->getType(),
        );

        $this->transactionLogRepository->add($transactionLog);
    }
}
