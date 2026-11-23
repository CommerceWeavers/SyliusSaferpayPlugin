<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Controller\Action\AfterUnsuccessfulPaymentAction;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\OrderProviderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AfterUnsuccessfulPaymentActionTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $orderProvider;

    private ObjectProphecy $router;

    private AfterUnsuccessfulPaymentAction $action;

    protected function setUp(): void
    {
        $this->orderProvider = $this->prophesize(OrderProviderInterface::class);
        $this->router = $this->prophesize(UrlGeneratorInterface::class);

        $this->action = new AfterUnsuccessfulPaymentAction(
            $this->orderProvider->reveal(),
            $this->router->reveal(),
        );
    }

    public function test_it_redirects_to_order_show_page_if_last_payment_is_new(): void
    {
        $request = $this->prophesize(Request::class);
        $order = $this->prophesize(OrderInterface::class);
        $lastPayment = $this->prophesize(PaymentInterface::class);
        $penultimatePayment = $this->prophesize(PaymentInterface::class);
        $session = $this->prophesize(Session::class);
        $flashBag = $this->prophesize(FlashBagInterface::class);

        $this->orderProvider->provide('TOKEN')->willReturn($order);
        $order->getPayments()->willReturn(new ArrayCollection([
            $penultimatePayment->reveal(),
            $lastPayment->reveal(),
        ]));

        $lastPayment->getState()->willReturn(PaymentInterface::STATE_NEW);
        $penultimatePayment->getState()->willReturn(PaymentInterface::STATE_CANCELLED);

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('info', 'sylius.payment.cancelled')->shouldBeCalled();

        $this->router->generate('sylius_shop_order_show', ['tokenValue' => 'TOKEN'])->willReturn('/orders/TOKEN');

        self::assertEquals(new RedirectResponse('/orders/TOKEN'), ($this->action)($request->reveal(), 'TOKEN'));
    }

    public function test_it_redirects_to_thank_you_page_if_last_payment_is_not_new(): void
    {
        $request = $this->prophesize(Request::class);
        $order = $this->prophesize(OrderInterface::class);
        $lastPayment = $this->prophesize(PaymentInterface::class);
        $penultimatePayment = $this->prophesize(PaymentInterface::class);
        $session = $this->prophesize(Session::class);
        $flashBag = $this->prophesize(FlashBagInterface::class);

        $this->orderProvider->provide('TOKEN')->willReturn($order);
        $order->getPayments()->willReturn(new ArrayCollection([
            $penultimatePayment->reveal(),
            $lastPayment->reveal(),
        ]));

        $lastPayment->getState()->willReturn(PaymentInterface::STATE_COMPLETED);

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('info', 'sylius.payment.completed')->shouldBeCalled();

        $this->router->generate('sylius_shop_order_thank_you')->willReturn('/thank-you');

        self::assertEquals(new RedirectResponse('/thank-you'), ($this->action)($request->reveal(), 'TOKEN'));
    }
}
