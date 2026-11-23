<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ResponseInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;

interface SaferpayClientInterface
{
    public function authorize(PaymentInterface $payment, TokenInterface $token): ResponseInterface;

    public function assert(PaymentInterface $payment): ResponseInterface;

    public function capture(PaymentInterface $payment): ResponseInterface;

    public function refund(PaymentInterface $payment): ResponseInterface;

    public function getTerminal(GatewayConfigInterface $gatewayConfig): array;
}
