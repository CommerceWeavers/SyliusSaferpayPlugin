<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider;

use Payum\Core\Payum;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Bundle\ResourceBundle\Controller\Parameters;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class TokenProviderSpec extends ObjectBehavior
{
    function let(Payum $payum): void
    {
        $this->beConstructedWith($payum);
    }

    function it_provides_token_for_assert_with_route_as_string(
        Payum $payum,
        RequestConfiguration $requestConfiguration,
        Parameters $parameters,
        GenericTokenFactoryInterface $tokenFactory,
        PaymentInterface $payment,
        TokenInterface $token,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $requestConfiguration->getParameters()->willReturn($parameters);
        $parameters->get('redirect')->willReturn('sylius_shop_order_thank_you');

        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getGatewayName()->willReturn('saferpay');

        $payum->getTokenFactory()->willReturn($tokenFactory);
        $tokenFactory
            ->createToken('saferpay', $payment->getWrappedObject(), 'sylius_shop_order_thank_you', [])
            ->willReturn($token)
        ;

        $this->provideForAssert($payment, $requestConfiguration)->shouldReturn($token);
    }

    function it_provides_token_for_assert_with_route_as_array(
        Payum $payum,
        RequestConfiguration $requestConfiguration,
        Parameters $parameters,
        GenericTokenFactoryInterface $tokenFactory,
        PaymentInterface $payment,
        TokenInterface $token,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $requestConfiguration->getParameters()->willReturn($parameters);
        $parameters->get('redirect')->willReturn([
            'route' => 'sylius_shop_order_thank_you',
            'parameters' => ['abc' => 'def'],
        ]);

        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getGatewayName()->willReturn('saferpay');

        $payum->getTokenFactory()->willReturn($tokenFactory);
        $tokenFactory
            ->createToken('saferpay', $payment->getWrappedObject(), 'sylius_shop_order_thank_you', ['abc' => 'def'])
            ->willReturn($token)
        ;

        $this->provideForAssert($payment, $requestConfiguration)->shouldReturn($token);
    }

    function it_provides_token_for_capture_with_route_as_string(
        Payum $payum,
        RequestConfiguration $requestConfiguration,
        Parameters $parameters,
        GenericTokenFactoryInterface $tokenFactory,
        PaymentInterface $payment,
        TokenInterface $token,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $requestConfiguration->getParameters()->willReturn($parameters);
        $parameters->get('redirect')->willReturn('sylius_shop_order_thank_you');

        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getGatewayName()->willReturn('saferpay');

        $payum->getTokenFactory()->willReturn($tokenFactory);
        $tokenFactory
            ->createCaptureToken('saferpay', $payment->getWrappedObject(), 'sylius_shop_order_thank_you', [])
            ->willReturn($token)
        ;

        $this->provideForCapture($payment, $requestConfiguration)->shouldReturn($token);
    }

    function it_provides_token_for_capture_with_route_as_array(
        Payum $payum,
        RequestConfiguration $requestConfiguration,
        Parameters $parameters,
        GenericTokenFactoryInterface $tokenFactory,
        PaymentInterface $payment,
        TokenInterface $token,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $requestConfiguration->getParameters()->willReturn($parameters);
        $parameters->get('redirect')->willReturn([
            'route' => 'sylius_shop_order_thank_you',
            'parameters' => ['abc' => 'def'],
        ]);

        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getGatewayName()->willReturn('saferpay');

        $payum->getTokenFactory()->willReturn($tokenFactory);
        $tokenFactory
            ->createCaptureToken('saferpay', $payment->getWrappedObject(), 'sylius_shop_order_thank_you', ['abc' => 'def'])
            ->willReturn($token)
        ;

        $this->provideForCapture($payment, $requestConfiguration)->shouldReturn($token);
    }

    function it_provides_token(
        Payum $payum,
        GenericTokenFactoryInterface $tokenFactory,
        PaymentInterface $payment,
        TokenInterface $token,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getGatewayName()->willReturn('saferpay');

        $payum->getTokenFactory()->willReturn($tokenFactory);
        $tokenFactory
            ->createToken('saferpay', $payment->getWrappedObject(), 'sylius_admin_order_show', ['id' => '1'])
            ->willReturn($token)
        ;

        $this->provide($payment, 'sylius_admin_order_show', ['id' => '1'])->shouldReturn($token);
    }
}
