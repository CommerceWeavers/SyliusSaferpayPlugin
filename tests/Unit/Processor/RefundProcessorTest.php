<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Processor;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\RefundFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\RefundInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Processor\RefundProcessor;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;

final class RefundProcessorTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $tokenProvider;

    private ObjectProphecy $payum;

    private ObjectProphecy $getStatusFactory;

    private ObjectProphecy $refundFactory;

    private RefundProcessor $processor;

    protected function setUp(): void
    {
        $this->tokenProvider = $this->prophesize(TokenProviderInterface::class);
        $this->payum = $this->prophesize(Payum::class);
        $this->getStatusFactory = $this->prophesize(GetStatusFactoryInterface::class);
        $this->refundFactory = $this->prophesize(RefundFactoryInterface::class);

        $this->processor = new RefundProcessor(
            $this->tokenProvider->reveal(),
            $this->payum->reveal(),
            $this->getStatusFactory->reveal(),
            $this->refundFactory->reveal(),
        );
    }

    public function test_it_executes_refund_process(): void
    {
        $httpRequestVerifier = $this->prophesize(HttpRequestVerifierInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $gateway = $this->prophesize(GatewayInterface::class);
        $getStatus = $this->prophesize(GetStatusInterface::class);
        $refund = $this->prophesize(RefundInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $order = $this->prophesize(OrderInterface::class);

        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getFactoryName()->willReturn('saferpay');
        $payment->getOrder()->willReturn($order);
        $order->getId()->willReturn('1');

        $this->tokenProvider->provide($payment, 'sylius_admin_order_show', ['id' => '1'])->willReturn($token);
        $token->getGatewayName()->willReturn('saferpay');

        $this->refundFactory->createNewWithModel($token)->willReturn($refund->reveal());
        $refund->getFirstModel()->willReturn($payment);

        $this->getStatusFactory->createNewWithModel($payment)->willReturn($getStatus);

        $this->payum->getGateway('saferpay')->willReturn($gateway);
        $gateway->execute($refund)->shouldBeCalled();
        $gateway->execute($getStatus)->shouldBeCalled()->willReturn($getStatus);

        $this->payum->getHttpRequestVerifier()->willReturn($httpRequestVerifier);
        $httpRequestVerifier->invalidate($token)->shouldBeCalled();

        $this->processor->process($payment->reveal());
    }

    public function test_it_does_nothing_for_non_saferpay_payments(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);

        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getFactoryName()->willReturn('offline');

        $this->payum->getGateway(Argument::any())->shouldNotBeCalled();
        $payment->getOrder()->shouldNotBeCalled();

        $this->processor->process($payment->reveal());
    }

    public function test_it_throws_an_exception_if_order_is_null(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);

        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getFactoryName()->willReturn('saferpay');
        $payment->getOrder()->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        $this->processor->process($payment->reveal());
    }
}
