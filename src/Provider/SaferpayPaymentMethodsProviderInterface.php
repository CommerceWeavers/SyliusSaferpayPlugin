<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Provider;

use Sylius\Component\Core\Model\PaymentMethodInterface;

interface SaferpayPaymentMethodsProviderInterface
{
    public function provide(PaymentMethodInterface $paymentMethod): array;
}
