<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Command;

use PhpSpec\ObjectBehavior;

final class ConfigurePaymentMethodsSpec extends ObjectBehavior
{
    public function it_represents_an_intention_of_configuring_payment_methods(): void
    {
        $this->beConstructedWith('payment_method_id', [1, 2]);

        $this->getPaymentMethodId()->shouldReturn('payment_method_id');
        $this->getPaymentMethods()->shouldReturn([1, 2]);
    }
}
