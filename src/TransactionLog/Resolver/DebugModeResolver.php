<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Resolver;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class DebugModeResolver implements DebugModeResolverInterface
{
    public function isEnabled(PaymentInterface $payment): bool
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        return $paymentMethod->getGatewayConfig()->getConfig()['debug'] ?? false;
    }
}
