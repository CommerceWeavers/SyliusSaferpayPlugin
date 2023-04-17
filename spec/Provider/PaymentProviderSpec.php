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

    function it_throws_an_exception_when_last_payment_with_new_state_does_not_exist_for_authorization(
        OrderProviderInterface $orderProvider,
        OrderInterface $order,
    ): void {
        $orderProvider->provideForAuthorization('TOKEN')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn(null);
        $order->getTokenValue()->willReturn('TOKEN');

        $this
            ->shouldThrow(new NotFoundHttpException('Order with token "TOKEN" does not have an active payment.'))
            ->during('provideForAuthorization', ['TOKEN'])
        ;
    }

    function it_throws_an_exception_when_last_payment_with_new_state_does_not_exist_for_capturing(
        OrderProviderInterface $orderProvider,
        OrderInterface $order,
    ): void {
        $orderProvider->provideForCapturing('TOKEN')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_AUTHORIZED)->willReturn(null);
        $order->getTokenValue()->willReturn('TOKEN');

        $this
            ->shouldThrow(new NotFoundHttpException('Order with token "TOKEN" does not have an active payment.'))
            ->during('provideForCapturing', ['TOKEN'])
        ;
    }

    function it_provides_last_payment_for_authorization(
        OrderProviderInterface $orderProvider,
        OrderInterface $order,
        PaymentInterface $payment,
    ): void {
        $orderProvider->provideForAuthorization('TOKEN')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment);

        $this->provideForAuthorization('TOKEN')->shouldReturn($payment);
    }

    function it_provides_last_payment_for_capturing(
        OrderProviderInterface $orderProvider,
        OrderInterface $order,
        PaymentInterface $payment,
    ): void {
        $orderProvider->provideForCapturing('TOKEN')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_AUTHORIZED)->willReturn($payment);

        $this->provideForCapturing('TOKEN')->shouldReturn($payment);
    }
}
