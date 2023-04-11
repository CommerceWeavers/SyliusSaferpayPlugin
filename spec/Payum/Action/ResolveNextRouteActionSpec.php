<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Authorize;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRoute;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class ResolveNextRouteActionSpec extends ObjectBehavior
{
    function it_supports_resolve_next_route_request_and_payment_model(SyliusPaymentInterface $payment): void
    {
        $request = new ResolveNextRoute($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(true);
    }

    function it_does_not_support_other_requests_than_resolve_next_action(SyliusPaymentInterface $payment): void
    {
        $request = new Authorize($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(false);
    }

    function it_does_not_support_capture_request_with_wrong_model(PayumPaymentInterface $payumPayment): void
    {
        $request = new ResolveNextRoute($payumPayment->getWrappedObject());

        $this->supports($request)->shouldReturn(false);
    }

    function it_throws_an_exception_when_request_not_supported_on_execute(): void
    {
        $this
            ->shouldThrow(RequestNotSupportedException::class)
            ->during('execute', [new ResolveNextRoute(new \stdClass())])
        ;
    }

    function it_sets_prepare_assert_route_when_payment_is_new(
        ResolveNextRoute $request,
        SyliusPaymentInterface $payment,
        OrderInterface $order,
    ): void {
        $payment->getDetails()->willReturn([
            'status' => StatusAction::STATUS_NEW,
        ]);
        $payment->getOrder()->willReturn($order);
        $order->getTokenValue()->willReturn('my_token');

        $request->getModel()->willReturn($payment);
        $request->setRouteName('commerce_weavers_sylius_saferpay_prepare_assert')->shouldBeCalled();
        $request->setRouteParameters(['tokenValue' => 'my_token'])->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }

    function it_sets_prepare_capture_route_when_payment_is_authorized(
        ResolveNextRoute $request,
        SyliusPaymentInterface $payment,
        OrderInterface $order,
    ): void {
        $payment->getDetails()->willReturn([
            'status' => StatusAction::STATUS_AUTHORIZED,
        ]);
        $payment->getOrder()->willReturn($order);
        $order->getTokenValue()->willReturn('my_token');

        $request->getModel()->willReturn($payment);
        $request->setRouteName('commerce_weavers_sylius_saferpay_prepare_capture')->shouldBeCalled();
        $request->setRouteParameters(['tokenValue' => 'my_token'])->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }

    function it_sets_thank_you_route_when_payments_is_captured(
        ResolveNextRoute $request,
        SyliusPaymentInterface $payment,
        OrderInterface $order,
    ): void
    {
        $payment->getDetails()->willReturn([
            'status' => StatusAction::STATUS_CAPTURED,
        ]);
        $payment->getState()->willReturn(SyliusPaymentInterface::STATE_COMPLETED);
        $payment->getOrder()->willReturn($order);
        $order->getTokenValue()->willReturn('my_token');

        $request->getModel()->willReturn($payment);
        $request->setRouteName('sylius_shop_order_thank_you')->shouldBeCalled();
        $request->setRouteParameters()->shouldNotBeCalled();

        $this->execute($request->getWrappedObject());
    }

    function it_sets_show_order_route_when_payment_status_cannot_be_matched(
        ResolveNextRoute $request,
        SyliusPaymentInterface $payment,
        OrderInterface $order,
    ): void
    {
        $payment->getDetails()->willReturn([
            'status' => 'unknown',
        ]);
        $payment->getState()->willReturn(SyliusPaymentInterface::STATE_COMPLETED);
        $payment->getOrder()->willReturn($order);
        $order->getTokenValue()->willReturn('my_token');

        $request->getModel()->willReturn($payment);
        $request->setRouteName('sylius_shop_order_show')->shouldBeCalled();
        $request->setRouteParameters(['tokenValue' => 'my_token'])->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }
}
