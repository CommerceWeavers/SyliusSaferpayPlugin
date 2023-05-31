<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface SaferpayClientInterface
{
    public function authorize(PaymentInterface $payment, TokenInterface $token): AuthorizeResponse;

    public function assert(PaymentInterface $payment): AssertResponse;

    public function capture(PaymentInterface $payment): CaptureResponse;

    public function refund(PaymentInterface $payment): RefundResponse;

    public function getTerminal(GatewayConfigInterface $gatewayConfig): array;
}
