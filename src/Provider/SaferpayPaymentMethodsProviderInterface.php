<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Provider;

interface SaferpayPaymentMethodsProviderInterface
{
    public function provide(): array;
}
