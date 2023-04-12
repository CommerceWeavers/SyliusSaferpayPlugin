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

    /**
     * @throws Exception
     */
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

        $resolveNextRouteFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($resolveNextRoute);
        $resolveNextRoute->getRouteName()->willReturn('sylius_shop_order_thank_you');
        $resolveNextRoute->getRouteParameters()->willReturn([]);

        $payum->getGateway('saferpay')->willReturn($gateway);

        $gateway->execute(Argument::type(AssertInterface::class))->shouldBeCalled();
        $gateway->execute(Argument::type(GetStatusInterface::class))->willReturn($getStatus);
        $gateway->execute(Argument::type(ResolveNextRouteInterface::class))->willReturn($resolveNextRoute);

        $router->generate('sylius_shop_order_thank_you', [])->willReturn('/thank-you');

        $response = $this($request);
        $response->shouldBeAnInstanceOf(RedirectResponse::class);
    }
}
