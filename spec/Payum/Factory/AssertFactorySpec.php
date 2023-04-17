<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\AssertFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Assert;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;

final class AssertFactorySpec extends ObjectBehavior
{
    function it_implements_assert_factory_interface(): void
    {
        $this->shouldImplement(AssertFactoryInterface::class);
    }

    function it_creates_new_assert_request(PaymentInterface $payment): void
    {
        $this->createNewWithModel($payment->getWrappedObject())->shouldBeLike(new Assert($payment->getWrappedObject()));
    }
}
