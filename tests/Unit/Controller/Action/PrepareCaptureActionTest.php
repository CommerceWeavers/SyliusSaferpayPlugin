<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Controller\Action\PrepareCaptureAction;
use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentAlreadyProcessedException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\PaymentProviderInterface;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PrepareCaptureActionTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $requestConfigurationFactory;

    private ObjectProphecy $orderMetadata;

    private ObjectProphecy $paymentProvider;

    private ObjectProphecy $tokenProvider;

    private ObjectProphecy $requestConfiguration;

    private ObjectProphecy $logger;

    private ObjectProphecy $router;

    private PrepareCaptureAction $action;

    protected function setUp(): void
    {
        $this->requestConfigurationFactory = $this->prophesize(RequestConfigurationFactoryInterface::class);
        $this->orderMetadata = $this->prophesize(MetadataInterface::class);
        $this->paymentProvider = $this->prophesize(PaymentProviderInterface::class);
        $this->tokenProvider = $this->prophesize(TokenProviderInterface::class);
        $this->requestConfiguration = $this->prophesize(RequestConfiguration::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->router = $this->prophesize(UrlGeneratorInterface::class);

        $this->requestConfigurationFactory->create($this->orderMetadata, Argument::type(Request::class))->willReturn($this->requestConfiguration);

        $this->action = new PrepareCaptureAction(
            $this->requestConfigurationFactory->reveal(),
            $this->orderMetadata->reveal(),
            $this->paymentProvider->reveal(),
            $this->tokenProvider->reveal(),
            $this->logger->reveal(),
            $this->router->reveal(),
        );
    }

    public function test_it_throws_an_exception_when_last_payment_for_given_order_token_value_does_not_exist(): void
    {
        $request = $this->prophesize(Request::class);

        $this->paymentProvider->provideForCapture('TOKEN')->willThrow(NotFoundHttpException::class);

        $this->expectException(NotFoundHttpException::class);

        ($this->action)($request->reveal(), 'TOKEN');
    }

    public function test_it_returns_to_thank_you_page_if_payment_is_already_processed(): void
    {
        $request = $this->prophesize(Request::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $session = $this->prophesize(Session::class);
        $flashBag = $this->prophesize(FlashBagInterface::class);

        $this->paymentProvider->provideForCapture('TOKEN')->willThrow(PaymentAlreadyProcessedException::class);

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('success', 'sylius.payment.completed')->shouldBeCalled();

        $this->router->generate('sylius_shop_order_thank_you')->willReturn('https://thank-you.com');

        self::assertEquals(new RedirectResponse('https://thank-you.com'), ($this->action)($request->reveal(), 'TOKEN'));
    }

    public function test_it_returns_redirect_response_to_target_url_from_token(): void
    {
        $request = $this->prophesize(Request::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $token = $this->prophesize(TokenInterface::class);

        $this->paymentProvider->provideForCapture('TOKEN')->willReturn($payment);
        $this->tokenProvider->provideForCapture($payment, $this->requestConfiguration)->willReturn($token);
        $token->getTargetUrl()->willReturn('/url');

        self::assertEquals(new RedirectResponse('/url'), ($this->action)($request->reveal(), 'TOKEN'));
    }
}
