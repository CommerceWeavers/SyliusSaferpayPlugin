<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use Payum\Core\Payum;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Bundle\ResourceBundle\Controller\Parameters;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PrepareCaptureActionSpec extends ObjectBehavior
{
    function let(
        RequestConfigurationFactoryInterface $requestConfigurationFactory,
        MetadataInterface $orderMetadata,
        OrderRepositoryInterface $orderRepository,
        Payum $payum,
        RequestConfiguration $requestConfiguration,
    ): void {
        $requestConfigurationFactory->create($orderMetadata, Argument::type(Request::class))->willReturn($requestConfiguration);

        $this->beConstructedWith($requestConfigurationFactory, $orderMetadata, $orderRepository, $payum);
    }

    function it_throws_an_exception_when_order_with_given_token_does_not_exist(
        OrderRepositoryInterface $orderRepository,
        Request $request,
    ): void {
        $orderRepository->findOneByTokenValue('mytoken')->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException('Order with token "mytoken" does not exist.'))
            ->during('__invoke', [$request, 'mytoken'])
        ;
    }

    function it_throws_an_exception_when_last_payment_with_new_state_does_not_exist(
        OrderRepositoryInterface $orderRepository,
        OrderInterface $order,
        Request $request,
    ): void {
        $orderRepository->findOneByTokenValue('mytoken')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_AUTHORIZED)->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException('Order with token "mytoken" does not have an active payment.'))
            ->during('__invoke', [$request, 'mytoken'])
        ;
    }

    function it_returns_redirect_response_to_target_url_from_token(
        OrderRepositoryInterface $orderRepository,
        Payum $payum,
        RequestConfiguration $requestConfiguration,
        Parameters $parameters,
        Request $request,
        GenericTokenFactoryInterface $tokenFactory,
        OrderInterface $order,
        PaymentInterface $payment,
        TokenInterface $token,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $requestConfiguration->getParameters()->willReturn($parameters);
        $parameters->get('redirect')->willReturn('sylius_shop_order_thank_you');

        $orderRepository->findOneByTokenValue('mytoken')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_AUTHORIZED)->willReturn($payment);

        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getGatewayName()->willReturn('saferpay');

        $payum->getTokenFactory()->willReturn($tokenFactory);
        $tokenFactory
            ->createCaptureToken('saferpay', $payment->getWrappedObject(), null, [],)
            ->willReturn($token)
        ;
        $token->getTargetUrl()->willReturn('/url');

        $this($request, 'mytoken')->shouldBeLike(new RedirectResponse('/url'));
    }
}
