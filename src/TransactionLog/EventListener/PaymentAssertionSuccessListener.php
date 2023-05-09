<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener;

use CommerceWeavers\SyliusSaferpayPlugin\Factory\TransactionLogFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAssertionSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\Exception\PaymentNotFoundException;
use Doctrine\Persistence\ObjectManager;
use Sylius\Calendar\Provider\DateTimeProviderInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;

final class PaymentAssertionSuccessListener
{
    public function __construct(
        private TransactionLogFactoryInterface $transactionLogFactory,
        private ObjectManager $transactionLogObjectManager,
        private PaymentRepositoryInterface $paymentRepository,
        private DateTimeProviderInterface $dateTimeProvider,
    ) {
    }

    /**
     * @throws PaymentNotFoundException
     */
    public function __invoke(PaymentAssertionSucceeded $event): void
    {
        /** @var PaymentInterface|null $payment */
        $payment = $this->paymentRepository->find($event->getPaymentId());

        if (null === $payment) {
            throw new PaymentNotFoundException($event->getPaymentId());
        }

        $transactionLog = $this->transactionLogFactory->createInformationalLog(
            $this->dateTimeProvider->now(),
            $payment,
            'Payment assertion succeeded',
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
