<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payment\EventDispatcher;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAssertionFailed;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAssertionSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAuthorizationSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentCaptureSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentRefundSucceeded;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class PaymentEventDispatcher implements PaymentEventDispatcherInterface
{
    public function __construct(private MessageBusInterface $eventBus)
    {
    }

    public function dispatchPaymentAuthorizationSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        AuthorizeResponse $response,
    ): void {
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new PaymentAuthorizationSucceeded($paymentId, $url, $request, $response->toArray()));
    }

    public function dispatchPaymentAssertionSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        AssertResponse $response,
    ): void {
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new PaymentAssertionSucceeded($paymentId, $url, $request, $response->toArray()));
    }

    public function dispatchPaymentAssertionFailedEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        AssertResponse $response,
    ): void {
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new PaymentAssertionFailed($paymentId, $url, $request, $response->toArray()));
    }

    public function dispatchPaymentCaptureSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        CaptureResponse $response,
    ): void {
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new PaymentCaptureSucceeded($paymentId, $url, $request, $response->toArray()));
    }

    public function dispatchPaymentRefundSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        RefundResponse $response,
    ): void {
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new PaymentRefundSucceeded($paymentId, $url, $request, $response->toArray()));
    }
}
