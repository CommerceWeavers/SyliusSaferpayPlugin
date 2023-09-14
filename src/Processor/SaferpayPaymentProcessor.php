<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Processor;

use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentAlreadyProcessedException;
use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentBeingProcessedException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\LockFactory;

final class SaferpayPaymentProcessor implements SaferpayPaymentProcessorInterface
{
    public function __construct(
        private LockFactory $lockFactory,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function lock(PaymentInterface $payment, string $targetState = 'NEW'): void
    {
        $this->logger->debug('Trying to lock payment:', ['id' => $payment->getId(), 'details' => $payment->getDetails()]);
        $lock = $this->lockFactory->createLock('payment_processing');

        try {
            if (!$lock->acquire()) {
                throw new PaymentBeingProcessedException();
            }
        } catch (LockConflictedException|LockAcquiringException) {
            throw new PaymentBeingProcessedException();
        }

        $paymentDetails = $payment->getDetails();

        if (!isset($paymentDetails['status'])) {
            $this->logger->debug('Payment processing aborted - payment already processed:', ['details' => $paymentDetails]);

            throw new PaymentAlreadyProcessedException();
        }

        if (
            (isset($paymentDetails['processing']) && $paymentDetails['processing'] === true) ||
            $paymentDetails['status'] !== $targetState
        ) {
            $this->logger->debug('Payment processing aborted - payment being processed:', ['details' => $paymentDetails]);

            throw new PaymentBeingProcessedException();
        }

        $payment->setDetails(array_merge($paymentDetails, ['processing' => true]));
        $this->entityManager->flush();

        $lock->release();
    }
}
