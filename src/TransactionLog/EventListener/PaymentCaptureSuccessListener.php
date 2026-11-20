<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentCaptureSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\Exception\PaymentNotFoundException;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Factory\TransactionLogFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Resolver\DebugModeResolverInterface;
use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\Clock\ClockInterface;

final class PaymentCaptureSuccessListener
{
    public function __construct(
        private TransactionLogFactoryInterface $transactionLogFactory,
        private ObjectManager $transactionLogObjectManager,
        private PaymentRepositoryInterface $paymentRepository,
        private ClockInterface $clock,
        private DebugModeResolverInterface $debugModeResolver,
    ) {
    }

    /**
     * @throws PaymentNotFoundException
     */
    public function __invoke(PaymentCaptureSucceeded $event): void
    {
        /** @var PaymentInterface|null $payment */
        $payment = $this->paymentRepository->find($event->getPaymentId());

        if (null === $payment) {
            throw new PaymentNotFoundException($event->getPaymentId());
        }

        if (!$this->debugModeResolver->isEnabled($payment)) {
            return;
        }

        $transactionLog = $this->transactionLogFactory->createInformationalLog(
            $this->clock->now(),
            $payment,
            'Payment capture succeeded',
            [
                'url' => $event->getRequestUrl(),
                'request' => $event->getRequestBody(),
                'response' => $event->getResponseData(),
            ],
        );

        $this->transactionLogObjectManager->persist($transactionLog);
        $this->transactionLogObjectManager->flush();
    }
}
