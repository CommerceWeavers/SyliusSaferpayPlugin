<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Provider;

use Sylius\Component\Core\Model\PaymentInterface;

interface PaymentProviderInterface
{
    public function provideForAssert(string $orderTokenValue): PaymentInterface;

    public function provideForCapture(string $orderTokenValue): PaymentInterface;
}
