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

final class PrepareAssertActionSpec extends ObjectBehavior
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

    function it_should_throw_exception_when_order_with_given_token_does_not_exist(
        Request $request,
        OrderRepositoryInterface $orderRepository,
    ): void {
        $orderRepository->findOneByTokenValue('mytoken')->willReturn(null);

        $this->shouldThrow(new NotFoundHttpException('Order with token "mytoken" does not exist.'))
            ->during('__invoke', [$request, 'mytoken'])
        ;
    }

    function it_should_throw_exception_when_last_payment_with_new_state_does_not_exist(
        Request $request,
        OrderRepositoryInterface $orderRepository,
        OrderInterface $order,
    ): void {
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn(null);

        $orderRepository->findOneByTokenValue('mytoken')->willReturn($order);

        $this->shouldThrow(new NotFoundHttpException('Order with token "mytoken" does not have an active payment.'))
            ->during('__invoke', [$request, 'mytoken'])
        ;
    }

    function it_should_return_redirect_response_to_target_url_from_token(
        RequestConfiguration $requestConfiguration,
        Parameters $parameters,
        Request $request,
        Payum $payum,
        OrderRepositoryInterface $orderRepository,
        GenericTokenFactoryInterface $tokenFactory,
        OrderInterface $order,
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

        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment);

        $orderRepository->findOneByTokenValue('mytoken')->willReturn($order);

        $payum->getTokenFactory()->willReturn($tokenFactory);
        $tokenFactory->createToken(
            Argument::type('string'),
            Argument::type(PaymentInterface::class),
            Argument::any(),
            Argument::type('array'),
        )->willReturn($token);
        $token->getTargetUrl()->willReturn('/url');

        $response = $this($request, 'mytoken');
        $response->shouldBeAnInstanceOf(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('/url');
    }
}
