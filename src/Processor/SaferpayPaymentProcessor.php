<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Processor;

use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentAlreadyProcessedException;
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
        $this->logger->debug('Trying to lock payment: ', ['id' => $payment->getId(), 'details' => $payment->getDetails()]);
        $lock = $this->lockFactory->createLock('payment_processing');

        try {
            if (!$lock->acquire()) {
                throw new PaymentAlreadyProcessedException();
            }
        } catch (LockConflictedException|LockAcquiringException) {
            throw new PaymentAlreadyProcessedException();
        }

        $paymentDetails = $payment->getDetails();

        if (
            (isset($paymentDetails['processing']) && $paymentDetails['processing'] === true) ||
            (isset($paymentDetails['status']) && $paymentDetails['status'] !== $targetState)
        ) {
            $this->logger->debug('Payment processing aborted: ', ['details' => $paymentDetails]);

            throw new PaymentAlreadyProcessedException();
        }

        $payment->setDetails(array_merge($paymentDetails, ['processing' => true]));
        $this->entityManager->flush();

        $lock->release();
    }
}
