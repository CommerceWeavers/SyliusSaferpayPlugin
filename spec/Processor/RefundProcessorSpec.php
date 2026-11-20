<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Processor;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\RefundFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\RefundInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\PaymentProviderInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\CommerceWeavers\SyliusSaferpayPlugin\Provider\Exception;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Sylius\Bundle\PayumBundle\Factory\ResolveNextRouteFactoryInterface;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRouteInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

final class RefundProcessorSpec extends ObjectBehavior
{
    function let(
        TokenProviderInterface $tokenProvider,
        Payum $payum,
        GetStatusFactoryInterface $getStatusFactory,
        RefundFactoryInterface $refundFactory,
    ): void {
        $this->beConstructedWith($tokenProvider, $payum, $getStatusFactory, $refundFactory);
    }

    function it_executes_refund_process(
        TokenProviderInterface $tokenProvider,
        Payum $payum,
        GetStatusFactoryInterface $getStatusFactory,
        RefundFactoryInterface $refundFactory,
        HttpRequestVerifierInterface $httpRequestVerifier,
        TokenInterface $token,
        GatewayInterface $gateway,
        GetStatusInterface $getStatus,
        RefundInterface $refund,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        OrderInterface $order,
    ): void {
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getFactoryName()->willReturn('saferpay');
        $payment->getOrder()->willReturn($order);
        $order->getId()->willReturn('1');

        $tokenProvider->provide($payment, 'sylius_admin_order_show', ['id' => '1'])->willReturn($token);
        $token->getGatewayName()->willReturn('saferpay');

        $refundFactory->createNewWithModel($token)->willReturn($refund->getWrappedObject());
        $refund->getFirstModel()->willReturn($payment);

        $getStatusFactory->createNewWithModel($payment)->willReturn($getStatus);

        $payum->getGateway('saferpay')->willReturn($gateway);
        $gateway->execute($refund)->shouldBeCalled();
        $gateway->execute($getStatus)->shouldBeCalled()->willReturn($getStatus);

        $payum->getHttpRequestVerifier()->willReturn($httpRequestVerifier);
        $httpRequestVerifier->invalidate($token)->shouldBeCalled();

        $this->process($payment);
    }

    function it_does_nothing_for_non_saferpay_payments(
        Payum $payum,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getFactoryName()->willReturn('offline');

        $payum->getGateway(Argument::any())->shouldNotBeCalled();
        $payment->getOrder()->shouldNotBeCalled();

        $this->process($payment);
    }

    function it_throws_an_exception_if_order_is_null(
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getFactoryName()->willReturn('saferpay');
        $payment->getOrder()->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('process', [$payment])
        ;
    }
}
