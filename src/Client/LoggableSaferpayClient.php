<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Entity\TransactionLogInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Event\SaferpayPaymentEvent;
use Payum\Core\Security\TokenInterface;
use Sylius\Calendar\Provider\DateTimeProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class LoggableSaferpayClient implements SaferpayClientInterface
{
    public function __construct(
        private SaferpayClientInterface $saferpayClient,
        private MessageBusInterface $eventBus,
        private DateTimeProviderInterface $dateTimeProvider,
    ) {
    }

    public function authorize(PaymentInterface $payment, TokenInterface $token): AuthorizeResponse
    {
        $authorizeResponse = $this->saferpayClient->authorize($payment, $token);

        /** @var int $paymentId */
        $paymentId = $payment->getId();
        $this->eventBus->dispatch(new SaferpayPaymentEvent(
            $this->dateTimeProvider->now(),
            $paymentId,
            'Payment authorization',
            $authorizeResponse->toArray(),
            $authorizeResponse->isSuccessful() ? TransactionLogInterface::TYPE_SUCCESS : TransactionLogInterface::TYPE_ERROR,
        ));

        return $authorizeResponse;
    }

    public function assert(PaymentInterface $payment): AssertResponse
    {
        $assertResponse = $this->saferpayClient->assert($payment);

        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new SaferpayPaymentEvent(
            $this->dateTimeProvider->now(),
            $paymentId,
            'Payment assertion',
            $assertResponse->toArray(),
            $assertResponse->isSuccessful() ? TransactionLogInterface::TYPE_SUCCESS : TransactionLogInterface::TYPE_ERROR,
        ));

        return $assertResponse;
    }

    public function capture(PaymentInterface $payment): CaptureResponse
    {
        $captureResponse = $this->saferpayClient->capture($payment);

        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new SaferpayPaymentEvent(
            $this->dateTimeProvider->now(),
            $paymentId,
            'Payment capture',
            $captureResponse->toArray(),
            $captureResponse->isSuccessful() ? TransactionLogInterface::TYPE_SUCCESS : TransactionLogInterface::TYPE_ERROR,
        ));

        return $captureResponse;
    }
}
