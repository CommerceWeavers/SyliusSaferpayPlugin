<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Processor;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\LockFactory;

final class SaferpayPaymentProcessor
{
    public function __construct(
        private LockFactory $lockFactory,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function lock(PaymentInterface $payment): void
    {
        $this->logger->debug('Trying to lock payment: ', ['id' => $payment->getId(), 'details' => $payment->getDetails()]);
        $lock = $this->lockFactory->createLock('payment_processing');

        try {
            if (!$lock->acquire()) {
                throw new \Exception('Payment already processed');
            }
        } catch (LockConflictedException|LockAcquiringException) {
            throw new \Exception('Payment already processed');
        }

        $paymentDetails = $payment->getDetails();

        if (
            (isset($paymentDetails['processing']) && $paymentDetails['processing'] === true) ||
            (isset($paymentDetails['status']) && $paymentDetails['status'] !== 'NEW')
        ) {
            $this->logger->debug('Payment processing aborted: ', ['details' => $paymentDetails]);

            throw new \Exception('Payment already processed');
        }

        $this->logger->debug('Payment is being processed: ', ['details' => $paymentDetails]);
        $payment->setDetails(array_merge($paymentDetails, ['processing' => true]));
        $this->logger->debug('Payment has been processed: ', ['details' => $payment->getDetails()]);
        $this->entityManager->flush();

        $lock->release();
    }
}
