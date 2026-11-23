<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Resolver;

use Sylius\Component\Payment\Model\GatewayConfigInterface;

interface SaferpayApiBaseUrlResolverInterface
{
    public function resolve(GatewayConfigInterface $gatewayConfig): string;
}
