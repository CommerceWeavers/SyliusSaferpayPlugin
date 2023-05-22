<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentCaptureSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\Exception\PaymentNotFoundException;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Resolver\DebugModeResolverInterface;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Factory\TransactionLogFactoryInterface;
use Doctrine\Persistence\ObjectManager;
use Sylius\Calendar\Provider\DateTimeProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;

final class PaymentCaptureSuccessListener
{
    public function __construct(
        private TransactionLogFactoryInterface $transactionLogFactory,
        private ObjectManager $transactionLogObjectManager,
        private PaymentRepositoryInterface $paymentRepository,
        private DateTimeProviderInterface $dateTimeProvider,
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
            $this->dateTimeProvider->now(),
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
