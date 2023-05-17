<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\RefundFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\RefundInterface;
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

final class RefundActionSpec extends ObjectBehavior
{
    function let(
        Payum $payum,
        GetStatusFactoryInterface $getStatusFactory,
        ResolveNextRouteFactoryInterface $resolveNextRouteFactory,
        RefundFactoryInterface $refundFactory,
        RouterInterface $router,
    ): void {
        $this->beConstructedWith($payum, $getStatusFactory, $resolveNextRouteFactory, $refundFactory, $router);
    }

    function it_returns_redirect_response_with_success_flash_message(
        Payum $payum,
        GetStatusFactoryInterface $getStatusFactory,
        ResolveNextRouteFactoryInterface $resolveNextRouteFactory,
        RefundFactoryInterface $refundFactory,
        RouterInterface $router,
        Request $request,
        HttpRequestVerifierInterface $httpRequestVerifier,
        TokenInterface $token,
        GatewayInterface $gateway,
        GetStatusInterface $getStatus,
        ResolveNextRouteInterface $resolveNextRoute,
        RefundInterface $refund,
        PaymentInterface $payment,
        Session $session,
        FlashBagInterface $flashBag,
    ): void {
        $payum->getHttpRequestVerifier()->willReturn($httpRequestVerifier);

        $token->getGatewayName()->willReturn('saferpay');
        $httpRequestVerifier->verify($request)->willReturn($token);
        $httpRequestVerifier->invalidate($token)->shouldBeCalled();

        $refundFactory->createNewWithModel($token)->willReturn($refund->getWrappedObject());
        $refund->getFirstModel()->willReturn($payment);

        $getStatusFactory->createNewWithModel($payment)->willReturn($getStatus);
        $getStatus->isRefunded()->willReturn(true);

        $resolveNextRouteFactory->createNewWithModel($payment)->willReturn($resolveNextRoute);
        $resolveNextRoute->getRouteName()->willReturn('sylius_admin_order_show');
        $resolveNextRoute->getRouteParameters()->willReturn(['id' => '1']);

        $payum->getGateway('saferpay')->willReturn($gateway);

        $gateway->execute($refund)->shouldBeCalled();
        $gateway->execute($getStatus)->shouldBeCalled()->willReturn($getStatus);
        $gateway->execute($resolveNextRoute)->shouldBeCalled()->willReturn($resolveNextRoute);

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('success', 'sylius.payment.refunded')->shouldBeCalled();

        $router->generate('sylius_admin_order_show', ['id' => '1'])->willReturn('/admin/orders/1');

        $this($request)->shouldBeLike(new RedirectResponse('/admin/orders/1'));
    }

    function it_returns_redirect_response_with_flash_message_about_failure(
        Payum $payum,
        GetStatusFactoryInterface $getStatusFactory,
        ResolveNextRouteFactoryInterface $resolveNextRouteFactory,
        RefundFactoryInterface $refundFactory,
        RouterInterface $router,
        Request $request,
        HttpRequestVerifierInterface $httpRequestVerifier,
        TokenInterface $token,
        GatewayInterface $gateway,
        GetStatusInterface $getStatus,
        ResolveNextRouteInterface $resolveNextRoute,
        RefundInterface $refund,
        PaymentInterface $payment,
        Session $session,
        FlashBagInterface $flashBag,
    ): void {
        $payum->getHttpRequestVerifier()->willReturn($httpRequestVerifier);

        $token->getGatewayName()->willReturn('saferpay');
        $httpRequestVerifier->verify($request)->willReturn($token);
        $httpRequestVerifier->invalidate($token)->shouldBeCalled();

        $refundFactory->createNewWithModel($token)->willReturn($refund->getWrappedObject());
        $refund->getFirstModel()->willReturn($payment);

        $getStatusFactory->createNewWithModel($payment)->willReturn($getStatus);
        $getStatus->isRefunded()->willReturn(false);
        $getStatus->isFailed()->willReturn(true);

        $resolveNextRouteFactory->createNewWithModel($payment)->willReturn($resolveNextRoute);
        $resolveNextRoute->getRouteName()->willReturn('sylius_admin_order_show');
        $resolveNextRoute->getRouteParameters()->willReturn(['id' => '1']);

        $payum->getGateway('saferpay')->willReturn($gateway);

        $gateway->execute($refund)->shouldBeCalled();
        $gateway->execute($getStatus)->shouldBeCalled()->willReturn($getStatus);
        $gateway->execute($resolveNextRoute)->shouldBeCalled()->willReturn($resolveNextRoute);

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('error', 'sylius_saferpay.payment.refund_failed')->shouldBeCalled();

        $router->generate('sylius_admin_order_show', ['id' => '1'])->willReturn('/admin/orders/1');

        $this($request)->shouldBeLike(new RedirectResponse('/admin/orders/1'));
    }

    function it_throws_an_exception_if_the_next_route_is_null(
        Payum $payum,
        GetStatusFactoryInterface $getStatusFactory,
        ResolveNextRouteFactoryInterface $resolveNextRouteFactory,
        RefundFactoryInterface $refundFactory,
        RouterInterface $router,
        Request $request,
        HttpRequestVerifierInterface $httpRequestVerifier,
        TokenInterface $token,
        GatewayInterface $gateway,
        GetStatusInterface $getStatus,
        ResolveNextRouteInterface $resolveNextRoute,
        RefundInterface $refund,
        PaymentInterface $payment,
        Session $session,
        FlashBagInterface $flashBag,
    ): void {
        $payum->getHttpRequestVerifier()->willReturn($httpRequestVerifier);

        $token->getGatewayName()->willReturn('saferpay');
        $httpRequestVerifier->verify($request)->willReturn($token);
        $httpRequestVerifier->invalidate($token)->shouldBeCalled();

        $refundFactory->createNewWithModel($token)->willReturn($refund->getWrappedObject());
        $refund->getFirstModel()->willReturn($payment);

        $getStatusFactory->createNewWithModel($payment)->willReturn($getStatus);
        $getStatus->isRefunded()->willReturn(false);
        $getStatus->isFailed()->willReturn(true);

        $resolveNextRouteFactory->createNewWithModel($payment)->willReturn($resolveNextRoute);
        $resolveNextRoute->getRouteName()->willReturn(null);
        $resolveNextRoute->getRouteParameters()->willReturn([]);

        $payum->getGateway('saferpay')->willReturn($gateway);

        $gateway->execute($refund)->shouldBeCalled();
        $gateway->execute($getStatus)->shouldBeCalled()->willReturn($getStatus);
        $gateway->execute($resolveNextRoute)->shouldBeCalled()->willReturn($resolveNextRoute);

        $request->getSession()->shouldNotBeCalled();

        $router->generate(Argument::any(), [])->shouldNotBeCalled();

        $this->shouldThrow(Exception::class)->during('__invoke', [$request]);
    }
}
