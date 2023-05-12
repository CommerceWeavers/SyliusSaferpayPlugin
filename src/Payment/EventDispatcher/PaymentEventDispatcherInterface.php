<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payment\EventDispatcher;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use Sylius\Component\Core\Model\PaymentInterface;

interface PaymentEventDispatcherInterface
{
    public function dispatchPaymentAuthorizationSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        AuthorizeResponse $response,
    ): void;

    public function dispatchPaymentAssertionSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        AssertResponse $response,
    ): void;

    public function dispatchPaymentAssertionFailedEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        AssertResponse $response,
    ): void;

    public function dispatchPaymentCaptureSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        CaptureResponse $response,
    ): void;

    public function dispatchPaymentRefundSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        RefundResponse $response,
    ): void;
}
