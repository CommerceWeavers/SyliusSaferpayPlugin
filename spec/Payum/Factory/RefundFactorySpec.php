<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\RefundFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Refund;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;

final class RefundFactorySpec extends ObjectBehavior
{
    function it_implements_refund_factory_interface(): void
    {
        $this->shouldImplement(RefundFactoryInterface::class);
    }

    function it_creates_new_refund_request(PaymentInterface $payment): void
    {
        $this->createNewWithModel($payment->getWrappedObject())->shouldBeLike(new Refund($payment->getWrappedObject()));
    }
}
