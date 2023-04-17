<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Provider;

use Sylius\Component\Core\Model\OrderInterface;

interface OrderProviderInterface
{
    public function provideForAuthorization(string $tokenValue): OrderInterface;

    public function provideForCapturing(string $tokenValue): OrderInterface;
}
