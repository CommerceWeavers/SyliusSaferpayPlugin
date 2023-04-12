<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Resolver;

use Payum\Core\Model\GatewayConfigInterface;

interface SaferpayApiBaseUrlResolverInterface
{
    public function resolve(GatewayConfigInterface $gatewayConfig): string;
}
