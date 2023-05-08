<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Provider;

use CommerceWeavers\SyliusSaferpayPlugin\Provider\OrderProviderInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PaymentProviderSpec extends ObjectBehavior
{
    function let(OrderProviderInterface $orderProvider): void
    {
        $this->beConstructedWith($orderProvider);
    }

    function it_throws_an_exception_when_last_payment_with_new_state_does_not_exist_for_assert(
        OrderProviderInterface $orderProvider,
        OrderInterface $order,
    ): void {
        $orderProvider->provideForAssert('TOKEN')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn(null);
        $order->getTokenValue()->willReturn('TOKEN');

        $this
            ->shouldThrow(new NotFoundHttpException('Order with token "TOKEN" does not have an active payment.'))
            ->during('provideForAssert', ['TOKEN'])
        ;
    }

    function it_throws_an_exception_when_last_payment_with_new_state_does_not_exist_for_capture(
        OrderProviderInterface $orderProvider,
        OrderInterface $order,
    ): void {
        $orderProvider->provideForCapture('TOKEN')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_AUTHORIZED)->willReturn(null);
        $order->getTokenValue()->willReturn('TOKEN');

        $this
            ->shouldThrow(new NotFoundHttpException('Order with token "TOKEN" does not have an active payment.'))
            ->during('provideForCapture', ['TOKEN'])
        ;
    }

    function it_provides_last_payment_for_assert(
        OrderProviderInterface $orderProvider,
        OrderInterface $order,
        PaymentInterface $payment,
    ): void {
        $orderProvider->provideForAssert('TOKEN')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment);

        $this->provideForAssert('TOKEN')->shouldReturn($payment);
    }

    function it_provides_last_payment_for_capture(
        OrderProviderInterface $orderProvider,
        OrderInterface $order,
        PaymentInterface $payment,
    ): void {
        $orderProvider->provideForCapture('TOKEN')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_AUTHORIZED)->willReturn($payment);

        $this->provideForCapture('TOKEN')->shouldReturn($payment);
    }
}
