<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payment\EventDispatcher;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use Sylius\Component\Core\Model\PaymentInterface;

interface PaymentEventDispatcherInterface
{
    public function dispatchAuthorizationSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        AuthorizeResponse $response,
    ): void;

    public function dispatchAuthorizationFailedEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        ErrorResponse $response,
    ): void;

    public function dispatchAssertionSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        AssertResponse $response,
    ): void;

    public function dispatchAssertionFailedEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        ErrorResponse $response,
    ): void;

    public function dispatchCaptureSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        CaptureResponse $response,
    ): void;

    public function dispatchCaptureFailedEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        CaptureResponse $response,
    ): void;

    public function dispatchRefundSucceededEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        RefundResponse $response,
    ): void;

    public function dispatchRefundFailedEvent(
        PaymentInterface $payment,
        string $url,
        array $request,
        ErrorResponse $response,
    ): void;
}
