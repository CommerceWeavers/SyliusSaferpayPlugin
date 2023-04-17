<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client;

use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface SaferpayClientInterface
{
    public function authorize(PaymentInterface $payment, TokenInterface $token): array;

    public function assert(PaymentInterface $payment): array;

    public function capture(PaymentInterface $payment): array;
}
