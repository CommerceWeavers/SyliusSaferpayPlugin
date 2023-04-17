<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client;

use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface SaferpayClientBodyFactoryInterface
{
    public function createForAuthorize(PaymentInterface $payment, TokenInterface $token): array;

    public function createForAssert(PaymentInterface $payment): array;

    public function createForCapture(PaymentInterface $payment): array;
}
