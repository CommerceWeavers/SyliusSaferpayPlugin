<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Resolver;

use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;

interface SaferpayApiBaseUrlResolverInterface
{
    public function resolve(GatewayConfigInterface $gatewayConfig): string;
}
