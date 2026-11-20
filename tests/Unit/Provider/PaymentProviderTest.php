<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Provider;

use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentAlreadyProcessedException;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\OrderProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\PaymentProvider;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class PaymentProviderTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<OrderProviderInterface> */
    private ObjectProphecy $orderProvider;

    /** @var ObjectProphecy<EntityManagerInterface> */
    private ObjectProphecy $entityManager;

    private PaymentProvider $paymentProvider;

    protected function setUp(): void
    {
        $this->orderProvider = $this->prophesize(OrderProviderInterface::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);

        $this->paymentProvider = new PaymentProvider($this->orderProvider->reveal(), $this->entityManager->reveal());
    }

    public function test_it_throws_an_exception_when_last_payment_with_new_state_does_not_exist_for_assert(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $this->orderProvider->provideForAssert('TOKEN')->willReturn($order->reveal());
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn(null);
        $order->getTokenValue()->willReturn('TOKEN');

        $this->expectException(PaymentAlreadyProcessedException::class);

        $this->paymentProvider->provideForAssert('TOKEN');
    }

    public function test_it_throws_an_exception_when_last_payment_with_new_state_does_not_exist_for_capture(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $this->orderProvider->provideForCapture('TOKEN')->willReturn($order->reveal());
        $order->getLastPayment(PaymentInterface::STATE_AUTHORIZED)->willReturn(null);
        $order->getTokenValue()->willReturn('TOKEN');

        $this->expectException(PaymentAlreadyProcessedException::class);

        $this->paymentProvider->provideForCapture('TOKEN');
    }

    public function test_it_provides_last_new_payment_for_assert(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $this->orderProvider->provideForAssert('TOKEN')->willReturn($order->reveal());
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment->reveal());

        $this->assertSame($payment->reveal(), $this->paymentProvider->provideForAssert('TOKEN'));
    }

    public function test_it_provides_last_authorized_payment_for_capture(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $this->orderProvider->provideForCapture('TOKEN')->willReturn($order->reveal());
        $order->getLastPayment(PaymentInterface::STATE_AUTHORIZED)->willReturn($payment->reveal());

        $this->assertSame($payment->reveal(), $this->paymentProvider->provideForCapture('TOKEN'));
    }

    public function test_it_provides_last_payment_for_order(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $this->orderProvider->provideForAssert('TOKEN')->willReturn($order->reveal());

        $this->entityManager->refresh($order->reveal())->shouldBeCalled();

        $order->getLastPayment()->willReturn($payment->reveal());

        $this->assertSame($payment->reveal(), $this->paymentProvider->provideForOrder('TOKEN'));
    }
}
