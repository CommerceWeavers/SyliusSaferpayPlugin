<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Resolver;

use Sylius\Component\Core\Model\PaymentInterface;

interface DebugModeResolverInterface
{
    public function isEnabled(PaymentInterface $payment): bool;
}
