<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Provider;

use Sylius\Component\Core\Model\PaymentInterface;

interface PaymentProviderInterface
{
    public function provideForAuthorization(string $orderTokenValue): PaymentInterface;

    public function provideForCapturing(string $orderTokenValue): PaymentInterface;
}
