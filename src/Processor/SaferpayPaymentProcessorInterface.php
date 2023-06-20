<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Processor;

use Sylius\Component\Core\Model\PaymentInterface;

interface SaferpayPaymentProcessorInterface
{
    public function lock(PaymentInterface $payment, string $targetState = 'NEW'): void;
}
