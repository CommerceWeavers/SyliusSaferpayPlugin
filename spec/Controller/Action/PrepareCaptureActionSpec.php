<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Provider\PaymentProviderInterface;
use Payum\Core\Payum;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Bundle\ResourceBundle\Controller\Parameters;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PrepareCaptureActionSpec extends ObjectBehavior
{
    function let(
        RequestConfigurationFactoryInterface $requestConfigurationFactory,
        MetadataInterface $orderMetadata,
        PaymentProviderInterface $paymentProvider,
        Payum $payum,
        RequestConfiguration $requestConfiguration,
    ): void {
        $requestConfigurationFactory->create($orderMetadata, Argument::type(Request::class))->willReturn($requestConfiguration);

        $this->beConstructedWith($requestConfigurationFactory, $orderMetadata, $paymentProvider, $payum);
    }

    function it_throws_an_exception_when_last_payment_for_given_order_token_value_does_not_exist(
        PaymentProviderInterface $paymentProvider,
        Request $request,
    ): void {
        $paymentProvider->provideForCapturing('TOKEN')->willThrow(NotFoundHttpException::class);

        $this->shouldThrow(NotFoundHttpException::class)->during('__invoke', [$request, 'TOKEN']);
    }

    function it_returns_redirect_response_to_target_url_from_token(
        PaymentProviderInterface $paymentProvider,
        Payum $payum,
        RequestConfiguration $requestConfiguration,
        Parameters $parameters,
        Request $request,
        GenericTokenFactoryInterface $tokenFactory,
        PaymentInterface $payment,
        TokenInterface $token,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $requestConfiguration->getParameters()->willReturn($parameters);
        $parameters->get('redirect')->willReturn(['route' => 'sylius_shop_order_thank_you']);

        $paymentProvider->provideForCapturing('TOKEN')->willReturn($payment);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getGatewayName()->willReturn('saferpay');

        $payum->getTokenFactory()->willReturn($tokenFactory);
        $tokenFactory
            ->createCaptureToken('saferpay', $payment->getWrappedObject(), 'sylius_shop_order_thank_you', [],)
            ->willReturn($token)
        ;
        $token->getTargetUrl()->willReturn('/url');

        $this($request, 'TOKEN')->shouldBeLike(new RedirectResponse('/url'));
    }
}
