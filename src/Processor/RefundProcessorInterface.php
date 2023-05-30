<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Processor;

use Sylius\Component\Core\Model\PaymentInterface;

interface RefundProcessorInterface
{
    public function process(PaymentInterface $payment): void;
}
