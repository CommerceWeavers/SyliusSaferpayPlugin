<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\ResolveNextRouteAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusCheckerInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Authorize;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRoute;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class ResolveNextRouteActionTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<StatusCheckerInterface> */
    private ObjectProphecy $statusChecker;

    /** @var ObjectProphecy<ResolveNextRoute> */
    private ObjectProphecy $request;

    /** @var ObjectProphecy<SyliusPaymentInterface> */
    private ObjectProphecy $payment;

    /** @var ObjectProphecy<OrderInterface> */
    private ObjectProphecy $order;

    private ResolveNextRouteAction $action;

    protected function setUp(): void
    {
        $this->statusChecker = $this->prophesize(StatusCheckerInterface::class);
        $this->request = $this->prophesize(ResolveNextRoute::class);
        $this->payment = $this->prophesize(SyliusPaymentInterface::class);
        $this->order = $this->prophesize(OrderInterface::class);

        $this->action = new ResolveNextRouteAction($this->statusChecker->reveal());
    }

    public function test_it_supports_resolve_next_route_request_and_payment_model(): void
    {
        $request = new ResolveNextRoute($this->payment->reveal());

        self::assertTrue($this->action->supports($request));
    }

    public function test_it_does_not_support_other_requests_than_resolve_next_route(): void
    {
        $request = new Authorize($this->payment->reveal());

        self::assertFalse($this->action->supports($request));
    }

    public function test_it_does_not_support_resolve_next_route_request_with_wrong_model(): void
    {
        $payumPayment = $this->prophesize(PayumPaymentInterface::class);
        $request = new ResolveNextRoute($payumPayment->reveal());

        self::assertFalse($this->action->supports($request));
    }

    public function test_it_throws_an_exception_when_request_not_supported_on_execute(): void
    {
        $this->expectException(RequestNotSupportedException::class);

        $this->action->execute(new ResolveNextRoute(new \stdClass()));
    }

    public function test_it_sets_prepare_assert_route_when_payment_is_new(): void
    {
        $payment = $this->payment->reveal();

        $this->statusChecker->isNew($payment)->willReturn(true);
        $this->payment->getOrder()->willReturn($this->order->reveal());
        $this->order->getTokenValue()->willReturn('TOKEN');

        $this->request->getModel()->willReturn($payment);
        $this->request->setRouteName('commerce_weavers_sylius_saferpay_prepare_assert')->shouldBeCalled();
        $this->request->setRouteParameters(['tokenValue' => 'TOKEN'])->shouldBeCalled();

        $this->action->execute($this->request->reveal());
    }

    public function test_it_sets_prepare_capture_route_when_payment_is_authorized(): void
    {
        $payment = $this->payment->reveal();

        $this->statusChecker->isNew($payment)->willReturn(false);
        $this->statusChecker->isAuthorized($payment)->willReturn(true);
        $this->payment->getOrder()->willReturn($this->order->reveal());
        $this->order->getTokenValue()->willReturn('TOKEN');

        $this->request->getModel()->willReturn($payment);
        $this->request->setRouteName('commerce_weavers_sylius_saferpay_prepare_capture')->shouldBeCalled();
        $this->request->setRouteParameters(['tokenValue' => 'TOKEN'])->shouldBeCalled();

        $this->action->execute($this->request->reveal());
    }

    public function test_it_sets_thank_you_route_when_payment_is_captured(): void
    {
        $payment = $this->payment->reveal();

        $this->statusChecker->isNew($payment)->willReturn(false);
        $this->statusChecker->isAuthorized($payment)->willReturn(false);
        $this->statusChecker->isCompleted($payment)->willReturn(true);

        $this->payment->getOrder()->willReturn($this->order->reveal());
        $this->order->getTokenValue()->willReturn('TOKEN');

        $this->request->getModel()->willReturn($payment);
        $this->request->setRouteName('sylius_shop_order_thank_you')->shouldBeCalled();
        $this->request->setRouteParameters(Argument::cetera())->shouldNotBeCalled();

        $this->action->execute($this->request->reveal());
    }

    public function test_it_sets_admin_show_order_route_when_payment_is_refunded(): void
    {
        $payment = $this->payment->reveal();

        $this->statusChecker->isNew($payment)->willReturn(false);
        $this->statusChecker->isAuthorized($payment)->willReturn(false);
        $this->statusChecker->isCompleted($payment)->willReturn(false);
        $this->statusChecker->isRefunded($payment)->willReturn(true);

        $this->payment->getOrder()->willReturn($this->order->reveal());
        $this->order->getId()->willReturn('1');

        $this->request->getModel()->willReturn($payment);
        $this->request->setRouteName('sylius_admin_order_show')->shouldBeCalled();
        $this->request->setRouteParameters(['id' => '1'])->shouldBeCalled();

        $this->action->execute($this->request->reveal());
    }

    public function test_it_sets_show_order_route_when_payment_status_cannot_be_matched(): void
    {
        $payment = $this->payment->reveal();

        $this->statusChecker->isNew($payment)->willReturn(false);
        $this->statusChecker->isAuthorized($payment)->willReturn(false);
        $this->statusChecker->isCaptured($payment)->willReturn(false);
        $this->statusChecker->isCompleted($payment)->willReturn(false);
        $this->statusChecker->isRefunded($payment)->willReturn(false);

        $this->payment->getOrder()->willReturn($this->order->reveal());
        $this->order->getTokenValue()->willReturn('TOKEN');

        $this->request->getModel()->willReturn($payment);
        $this->request->setRouteName('sylius_shop_order_show')->shouldBeCalled();
        $this->request->setRouteParameters(['tokenValue' => 'TOKEN'])->shouldBeCalled();

        $this->action->execute($this->request->reveal());
    }
}
