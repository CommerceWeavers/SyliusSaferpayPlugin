<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Command;

use CommerceWeavers\SyliusSaferpayPlugin\Command\ConfigurePaymentMethods;
use PHPUnit\Framework\TestCase;

final class ConfigurePaymentMethodsTest extends TestCase
{
    public function test_it_represents_an_intention_of_configuring_payment_methods(): void
    {
        $command = new ConfigurePaymentMethods('payment_method_id', [1, 2]);

        self::assertSame('payment_method_id', $command->getPaymentMethodId());
        self::assertSame([1, 2], $command->getPaymentMethods());
    }
}
