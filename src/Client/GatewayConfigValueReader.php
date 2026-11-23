<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client;

use Sylius\Component\Payment\Model\GatewayConfigInterface;

/** @internal */
final class GatewayConfigValueReader
{
    /**
     * A missing value is returned as an empty string, so Saferpay answers with a handled error response.
     */
    public static function getString(GatewayConfigInterface $gatewayConfig, string $key): string
    {
        $value = $gatewayConfig->getConfig()[$key] ?? null;

        return is_scalar($value) ? (string) $value : '';
    }
}
