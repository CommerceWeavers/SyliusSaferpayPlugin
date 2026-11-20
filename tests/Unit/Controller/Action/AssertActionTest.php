<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Controller\Action\AssertAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\AssertFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\AssertInterface;
use Exception;
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
use Sylius\Bundle\PayumBundle\Factory\ResolveNextRouteFactoryInterface;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRouteInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;

final class AssertActionTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $payum;

    private ObjectProphecy $getStatusFactory;

    private ObjectProphecy $resolveNextRouteFactory;

    private ObjectProphecy $assertFactory;

    private ObjectProphecy $router;

    private AssertAction $action;

    protected function setUp(): void
    {
        $this->payum = $this->prophesize(Payum::class);
        $this->getStatusFactory = $this->prophesize(GetStatusFactoryInterface::class);
        $this->resolveNextRouteFactory = $this->prophesize(ResolveNextRouteFactoryInterface::class);
        $this->assertFactory = $this->prophesize(AssertFactoryInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);

        $this->action = new AssertAction(
            $this->payum->reveal(),
            $this->getStatusFactory->reveal(),
            $this->resolveNextRouteFactory->reveal(),
            $this->assertFactory->reveal(),
            $this->router->reveal(),
        );
    }

    public function test_it_returns_redirect_response(): void
    {
        $request = $this->prophesize(Request::class);
        $httpRequestVerifier = $this->prophesize(HttpRequestVerifierInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $gateway = $this->prophesize(GatewayInterface::class);
        $getStatus = $this->prophesize(GetStatusInterface::class);
        $resolveNextRoute = $this->prophesize(ResolveNextRouteInterface::class);
        $assert = $this->prophesize(AssertInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);

        $this->payum->getHttpRequestVerifier()->willReturn($httpRequestVerifier);

        $token->getGatewayName()->willReturn('saferpay');
        $httpRequestVerifier->verify($request)->willReturn($token);
        $httpRequestVerifier->invalidate($token)->shouldBeCalled();

        $this->assertFactory->createNewWithModel(Argument::type(TokenInterface::class))->willReturn($assert->reveal());
        $assert->getFirstModel()->willReturn($payment);

        $this->getStatusFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($getStatus);
        $getStatus->isCanceled()->willReturn(false);
        $getStatus->isFailed()->willReturn(false);

        $this->resolveNextRouteFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($resolveNextRoute);
        $resolveNextRoute->getRouteName()->willReturn('sylius_shop_order_thank_you');
        $resolveNextRoute->getRouteParameters()->willReturn([]);

        $this->payum->getGateway('saferpay')->willReturn($gateway);

        $gateway->execute(Argument::type(AssertInterface::class))->shouldBeCalled();
        $gateway->execute(Argument::type(GetStatusInterface::class))->willReturn($getStatus);
        $gateway->execute(Argument::type(ResolveNextRouteInterface::class))->willReturn($resolveNextRoute);

        $request->getSession()->shouldNotBeCalled();

        $this->router->generate('sylius_shop_order_thank_you', [])->willReturn('/thank-you');

        self::assertEquals(new RedirectResponse('/thank-you'), ($this->action)($request->reveal()));
    }

    public function test_it_returns_redirect_response_with_flash_message_about_failure(): void
    {
        $request = $this->prophesize(Request::class);
        $httpRequestVerifier = $this->prophesize(HttpRequestVerifierInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $gateway = $this->prophesize(GatewayInterface::class);
        $getStatus = $this->prophesize(GetStatusInterface::class);
        $resolveNextRoute = $this->prophesize(ResolveNextRouteInterface::class);
        $assert = $this->prophesize(AssertInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $session = $this->prophesize(Session::class);
        $flashBag = $this->prophesize(FlashBagInterface::class);

        $this->payum->getHttpRequestVerifier()->willReturn($httpRequestVerifier);

        $token->getGatewayName()->willReturn('saferpay');
        $httpRequestVerifier->verify($request)->willReturn($token);
        $httpRequestVerifier->invalidate($token)->shouldBeCalled();

        $this->assertFactory->createNewWithModel(Argument::type(TokenInterface::class))->willReturn($assert->reveal());
        $assert->getFirstModel()->willReturn($payment);

        $this->getStatusFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($getStatus);
        $getStatus->isCanceled()->willReturn(false);
        $getStatus->isFailed()->willReturn(true);

        $this->resolveNextRouteFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($resolveNextRoute);
        $resolveNextRoute->getRouteName()->willReturn('sylius_shop_order_show');
        $resolveNextRoute->getRouteParameters()->willReturn([]);

        $this->payum->getGateway('saferpay')->willReturn($gateway);

        $gateway->execute(Argument::type(AssertInterface::class))->shouldBeCalled();
        $gateway->execute(Argument::type(GetStatusInterface::class))->willReturn($getStatus);
        $gateway->execute(Argument::type(ResolveNextRouteInterface::class))->willReturn($resolveNextRoute);

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('error', 'sylius.payment.failed')->shouldBeCalled();

        $this->router->generate('sylius_shop_order_show', [])->willReturn('/TOKEN');

        self::assertEquals(new RedirectResponse('/TOKEN'), ($this->action)($request->reveal()));
    }

    public function test_it_returns_redirect_response_with_flash_message_about_cancellation(): void
    {
        $request = $this->prophesize(Request::class);
        $httpRequestVerifier = $this->prophesize(HttpRequestVerifierInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $gateway = $this->prophesize(GatewayInterface::class);
        $getStatus = $this->prophesize(GetStatusInterface::class);
        $resolveNextRoute = $this->prophesize(ResolveNextRouteInterface::class);
        $assert = $this->prophesize(AssertInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $session = $this->prophesize(Session::class);
        $flashBag = $this->prophesize(FlashBagInterface::class);

        $this->payum->getHttpRequestVerifier()->willReturn($httpRequestVerifier);

        $token->getGatewayName()->willReturn('saferpay');
        $httpRequestVerifier->verify($request)->willReturn($token);
        $httpRequestVerifier->invalidate($token)->shouldBeCalled();

        $this->assertFactory->createNewWithModel(Argument::type(TokenInterface::class))->willReturn($assert->reveal());
        $assert->getFirstModel()->willReturn($payment);

        $this->getStatusFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($getStatus);
        $getStatus->isCanceled()->willReturn(true);

        $this->resolveNextRouteFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($resolveNextRoute);
        $resolveNextRoute->getRouteName()->willReturn('sylius_shop_order_show');
        $resolveNextRoute->getRouteParameters()->willReturn([]);

        $this->payum->getGateway('saferpay')->willReturn($gateway);

        $gateway->execute(Argument::type(AssertInterface::class))->shouldBeCalled();
        $gateway->execute(Argument::type(GetStatusInterface::class))->willReturn($getStatus);
        $gateway->execute(Argument::type(ResolveNextRouteInterface::class))->willReturn($resolveNextRoute);

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('info', 'sylius.payment.cancelled')->shouldBeCalled();

        $this->router->generate('sylius_shop_order_show', [])->willReturn('/TOKEN');

        self::assertEquals(new RedirectResponse('/TOKEN'), ($this->action)($request->reveal()));
    }

    public function test_it_throws_an_exception_if_the_next_route_is_null(): void
    {
        $request = $this->prophesize(Request::class);
        $httpRequestVerifier = $this->prophesize(HttpRequestVerifierInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $gateway = $this->prophesize(GatewayInterface::class);
        $getStatus = $this->prophesize(GetStatusInterface::class);
        $resolveNextRoute = $this->prophesize(ResolveNextRouteInterface::class);
        $assert = $this->prophesize(AssertInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);

        $this->payum->getHttpRequestVerifier()->willReturn($httpRequestVerifier);

        $token->getGatewayName()->willReturn('saferpay');
        $httpRequestVerifier->verify($request)->willReturn($token);
        $httpRequestVerifier->invalidate($token)->shouldBeCalled();

        $this->assertFactory->createNewWithModel(Argument::type(TokenInterface::class))->willReturn($assert->reveal());
        $assert->getFirstModel()->willReturn($payment);

        $this->getStatusFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($getStatus);

        $this->resolveNextRouteFactory->createNewWithModel(Argument::type(PaymentInterface::class))->willReturn($resolveNextRoute);
        $resolveNextRoute->getRouteName()->willReturn(null);
        $resolveNextRoute->getRouteParameters()->willReturn([]);

        $this->payum->getGateway('saferpay')->willReturn($gateway);

        $gateway->execute(Argument::type(AssertInterface::class))->shouldBeCalled();
        $gateway->execute(Argument::type(GetStatusInterface::class))->willReturn($getStatus);
        $gateway->execute(Argument::type(ResolveNextRouteInterface::class))->willReturn($resolveNextRoute);

        $this->router->generate(Argument::any(), [])->shouldNotBeCalled();

        $this->expectException(Exception::class);

        ($this->action)($request->reveal());
    }
}
