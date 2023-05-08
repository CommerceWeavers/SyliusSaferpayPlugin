<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Provider;

use Sylius\Component\Core\Model\OrderInterface;

interface OrderProviderInterface
{
    public function provideForAssert(string $tokenValue): OrderInterface;

    public function provideForCapture(string $tokenValue): OrderInterface;
}
