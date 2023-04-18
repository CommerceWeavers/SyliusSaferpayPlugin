<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Event\Handler;

use CommerceWeavers\SyliusSaferpayPlugin\Entity\TransactionLogInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Event\SaferpayPaymentEvent;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class SaferpayPaymentEventHandler
{
    public function __construct(
        private FactoryInterface $transactionLogFactory,
        private RepositoryInterface $transactionLogRepository,
        private PaymentRepositoryInterface $paymentRepository,
    ) {
    }

    public function __invoke(SaferpayPaymentEvent $event): void
    {
        /** @var PaymentInterface|null $payment */
        $payment = $this->paymentRepository->find($event->getPaymentId());

        Assert::notNull($payment, 'Payment not found');

        /** @var TransactionLogInterface $transactionLog */
        $transactionLog = $this->transactionLogFactory->createNew();
        $transactionLog->setCreatedAt($event->getCreatedAt());
        $transactionLog->setPayment($payment);
        $transactionLog->setStatus($event->getStatus());
        $transactionLog->setDescription($event->getDescription());
        $transactionLog->setContext($event->getContext());

        $this->transactionLogRepository->add($transactionLog);
    }
}
