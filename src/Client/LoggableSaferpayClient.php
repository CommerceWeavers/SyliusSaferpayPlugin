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
            [
                'request_id' => $authorizeResponse->getResponseHeader()->getRequestId(),
                'saferpay_token' => $authorizeResponse->getToken(),
            ],
            TransactionLogInterface::TYPE_SUCCESS,
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
            [
                'request_id' => $assertResponse->getResponseHeader()->getRequestId(),
                'transaction_id' => $assertResponse->getTransaction()?->getId(),
                'transaction_status' => $assertResponse->getTransaction()?->getStatus(),
            ],
            TransactionLogInterface::TYPE_SUCCESS,
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
            [
                'request_id' => $captureResponse->getResponseHeader()->getRequestId(),
                'capture_id' => $captureResponse->getCaptureId(),
                'capture_status' => $captureResponse->getStatus(),
            ],
            TransactionLogInterface::TYPE_SUCCESS,
        ));

        return $captureResponse;
    }
}
