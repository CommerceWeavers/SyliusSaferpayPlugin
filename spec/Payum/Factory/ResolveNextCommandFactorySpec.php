<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\ResolveNextCommandFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\ResolveNextCommand;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;

final class ResolveNextCommandFactorySpec extends ObjectBehavior
{
    function it_implements_resolve_next_command_factory_interface(): void
    {
        $this->shouldImplement(ResolveNextCommandFactoryInterface::class);
    }

    function it_creates_new_resolve_next_command_request(PaymentInterface $payment): void
    {
        $this->createNewWithModel($payment->getWrappedObject())->shouldBeLike(new ResolveNextCommand($payment->getWrappedObject()));
    }
}
