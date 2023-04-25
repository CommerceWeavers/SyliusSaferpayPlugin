<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\AssertFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\AssertInterface;
use Exception;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Sylius\Bundle\PayumBundle\Factory\ResolveNextRouteFactoryInterface;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRouteInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;

final class AssertActionSpec extends ObjectBehavior
{
    function let(
        Payum $payum,
        GetStatusFactoryInterface $getStatusFactory,
        ResolveNextRouteFactoryInterface $resolveNextRouteFactory,
        AssertFactoryInterface $assertFactory,
        RouterInterface $router,
    ): void {
        $this->beConstructedWith($payum, $getStatusFactory, $resolveNextRouteFactory, $assertFactory, $router);
    }

    function it_returns_redirect_response(
        Payum $payum,
        GetStatusFactoryInterface $getStatusFactory,
        ResolveNextRouteFactoryInterface $resolveNextRouteFactory,
        AssertFactoryInterface $assertFactory,
        RouterInterface $router,
        Request $request,
        HttpRequestVerifierInterface $httpRequestVerifier,
        TokenInterface $token,
        GatewayInterface $gateway,
        GetStatusInterface $getStatus,
        ResolveNextRouteInterface $resolveNextRoute,
        AssertInterface $assert,
        PaymentInterface $payment,
    ): void {
        $payum->getHttpRequestVerifier()->willReturn($httpRequestVerifier);

        $token->getGatewayName()->willReturn('saferpay');
        $httpRequestVerifier->verify($request)->willReturn($token);
        $httpRequestVerifier->invalidate($token)->shouldBeCalled();

        $assertFactory->createNewWithModel(Argument::type(TokenInterface::class))->willReturn($assert->getWrappedObject());
        $assert->getFirstModel()->willReturn($payment);

        $getStatusFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($getStatus);
        $getStatus->isCanceled()->willReturn(false);
        $getStatus->isFailed()->willReturn(false);

        $resolveNextRouteFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($resolveNextRoute);
        $resolveNextRoute->getRouteName()->willReturn('sylius_shop_order_thank_you');
        $resolveNextRoute->getRouteParameters()->willReturn([]);

        $payum->getGateway('saferpay')->willReturn($gateway);

        $gateway->execute(Argument::type(AssertInterface::class))->shouldBeCalled();
        $gateway->execute(Argument::type(GetStatusInterface::class))->willReturn($getStatus);
        $gateway->execute(Argument::type(ResolveNextRouteInterface::class))->willReturn($resolveNextRoute);

        $request->getSession()->shouldNotBeCalled();

        $router->generate('sylius_shop_order_thank_you', [])->willReturn('/thank-you');

        $this($request)->shouldBeLike(new RedirectResponse('/thank-you'));
    }

    function it_returns_redirect_response_with_flash_message_about_failure(
        Payum $payum,
        GetStatusFactoryInterface $getStatusFactory,
        ResolveNextRouteFactoryInterface $resolveNextRouteFactory,
        AssertFactoryInterface $assertFactory,
        RouterInterface $router,
        Request $request,
        HttpRequestVerifierInterface $httpRequestVerifier,
        TokenInterface $token,
        GatewayInterface $gateway,
        GetStatusInterface $getStatus,
        ResolveNextRouteInterface $resolveNextRoute,
        AssertInterface $assert,
        PaymentInterface $payment,
        Session $session,
        FlashBagInterface $flashBag,
    ): void {
        $payum->getHttpRequestVerifier()->willReturn($httpRequestVerifier);

        $token->getGatewayName()->willReturn('saferpay');
        $httpRequestVerifier->verify($request)->willReturn($token);
        $httpRequestVerifier->invalidate($token)->shouldBeCalled();

        $assertFactory->createNewWithModel(Argument::type(TokenInterface::class))->willReturn($assert->getWrappedObject());
        $assert->getFirstModel()->willReturn($payment);

        $getStatusFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($getStatus);
        $getStatus->isCanceled()->willReturn(false);
        $getStatus->isFailed()->willReturn(true);

        $resolveNextRouteFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($resolveNextRoute);
        $resolveNextRoute->getRouteName()->willReturn('sylius_shop_order_show');
        $resolveNextRoute->getRouteParameters()->willReturn([]);

        $payum->getGateway('saferpay')->willReturn($gateway);

        $gateway->execute(Argument::type(AssertInterface::class))->shouldBeCalled();
        $gateway->execute(Argument::type(GetStatusInterface::class))->willReturn($getStatus);
        $gateway->execute(Argument::type(ResolveNextRouteInterface::class))->willReturn($resolveNextRoute);

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('error', 'sylius.payment.failed')->shouldBeCalled();

        $router->generate('sylius_shop_order_show', [])->willReturn('/TOKEN');

        $this($request)->shouldBeLike(new RedirectResponse('/TOKEN'));
    }

    function it_returns_redirect_response_with_flash_message_about_cancellation(
        Payum $payum,
        GetStatusFactoryInterface $getStatusFactory,
        ResolveNextRouteFactoryInterface $resolveNextRouteFactory,
        AssertFactoryInterface $assertFactory,
        RouterInterface $router,
        Request $request,
        HttpRequestVerifierInterface $httpRequestVerifier,
        TokenInterface $token,
        GatewayInterface $gateway,
        GetStatusInterface $getStatus,
        ResolveNextRouteInterface $resolveNextRoute,
        AssertInterface $assert,
        PaymentInterface $payment,
        Session $session,
        FlashBagInterface $flashBag,
    ): void {
        $payum->getHttpRequestVerifier()->willReturn($httpRequestVerifier);

        $token->getGatewayName()->willReturn('saferpay');
        $httpRequestVerifier->verify($request)->willReturn($token);
        $httpRequestVerifier->invalidate($token)->shouldBeCalled();

        $assertFactory->createNewWithModel(Argument::type(TokenInterface::class))->willReturn($assert->getWrappedObject());
        $assert->getFirstModel()->willReturn($payment);

        $getStatusFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($getStatus);
        $getStatus->isCanceled()->willReturn(true);

        $resolveNextRouteFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($resolveNextRoute);
        $resolveNextRoute->getRouteName()->willReturn('sylius_shop_order_show');
        $resolveNextRoute->getRouteParameters()->willReturn([]);

        $payum->getGateway('saferpay')->willReturn($gateway);

        $gateway->execute(Argument::type(AssertInterface::class))->shouldBeCalled();
        $gateway->execute(Argument::type(GetStatusInterface::class))->willReturn($getStatus);
        $gateway->execute(Argument::type(ResolveNextRouteInterface::class))->willReturn($resolveNextRoute);

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('error', 'sylius.payment.cancelled')->shouldBeCalled();

        $router->generate('sylius_shop_order_show', [])->willReturn('/TOKEN');

        $this($request)->shouldBeLike(new RedirectResponse('/TOKEN'));
    }

    function it_throws_an_exception_if_the_next_route_is_null(
        Payum $payum,
        GetStatusFactoryInterface $getStatusFactory,
        ResolveNextRouteFactoryInterface $resolveNextRouteFactory,
        AssertFactoryInterface $assertFactory,
        RouterInterface $router,
        Request $request,
        HttpRequestVerifierInterface $httpRequestVerifier,
        TokenInterface $token,
        GatewayInterface $gateway,
        GetStatusInterface $getStatus,
        ResolveNextRouteInterface $resolveNextRoute,
        AssertInterface $assert,
        PaymentInterface $payment,
    ): void {
        $payum->getHttpRequestVerifier()->willReturn($httpRequestVerifier);

        $token->getGatewayName()->willReturn('saferpay');
        $httpRequestVerifier->verify($request)->willReturn($token);
        $httpRequestVerifier->invalidate($token)->shouldBeCalled();

        $assertFactory->createNewWithModel(Argument::type(TokenInterface::class))->willReturn($assert->getWrappedObject());
        $assert->getFirstModel()->willReturn($payment);

        $getStatusFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($getStatus);

        $resolveNextRouteFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($resolveNextRoute);
        $resolveNextRoute->getRouteName()->willReturn(null);
        $resolveNextRoute->getRouteParameters()->willReturn([]);

        $payum->getGateway('saferpay')->willReturn($gateway);

        $gateway->execute(Argument::type(AssertInterface::class))->shouldBeCalled();
        $gateway->execute(Argument::type(GetStatusInterface::class))->willReturn($getStatus);
        $gateway->execute(Argument::type(ResolveNextRouteInterface::class))->willReturn($resolveNextRoute);

        $router->generate(Argument::any(), [])->shouldNotBeCalled();

        $this->shouldThrow(Exception::class)->during('__invoke', [$request]);
    }
}
