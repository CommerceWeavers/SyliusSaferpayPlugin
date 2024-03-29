<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payment\EventDispatcher;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAssertionFailed;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAssertionSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAuthorizationFailed;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAuthorizationSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentCaptureFailed;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentCaptureSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentRefundFailed;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentRefundSucceeded;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class PaymentEventDispatcher implements PaymentEventDispatcherInterface
{
    public function __construct(private MessageBusInterface $eventBus)
    {
    }

    public function dispatchAuthorizationSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        AuthorizeResponse $response,
    ): void {
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new PaymentAuthorizationSucceeded($paymentId, $url, $request, $response->toArray()));
    }

    public function dispatchAuthorizationFailedEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        ErrorResponse $response,
    ): void {
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new PaymentAuthorizationFailed($paymentId, $url, $request, $response->toArray()));
    }

    public function dispatchAssertionSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        AssertResponse $response,
    ): void {
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new PaymentAssertionSucceeded($paymentId, $url, $request, $response->toArray()));
    }

    public function dispatchAssertionFailedEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        ErrorResponse $response,
    ): void {
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new PaymentAssertionFailed($paymentId, $url, $request, $response->toArray()));
    }

    public function dispatchCaptureSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        CaptureResponse $response,
    ): void {
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new PaymentCaptureSucceeded($paymentId, $url, $request, $response->toArray()));
    }

    public function dispatchCaptureFailedEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        ErrorResponse $response,
    ): void {
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new PaymentCaptureFailed($paymentId, $url, $request, $response->toArray()));
    }

    public function dispatchRefundSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        RefundResponse $response,
    ): void {
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new PaymentRefundSucceeded($paymentId, $url, $request, $response->toArray()));
    }

    public function dispatchRefundFailedEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        ErrorResponse $response,
    ): void {
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->eventBus->dispatch(new PaymentRefundFailed($paymentId, $url, $request, $response->toArray()));
    }
}
