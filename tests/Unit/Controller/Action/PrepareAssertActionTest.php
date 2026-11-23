<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Controller\Action\PrepareAssertAction;
use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentAlreadyProcessedException;
use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentBeingProcessedException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Processor\SaferpayPaymentProcessorInterface;
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
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PrepareAssertActionTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $requestConfigurationFactory;

    private ObjectProphecy $orderMetadata;

    private ObjectProphecy $paymentProvider;

    private ObjectProphecy $tokenProvider;

    private ObjectProphecy $requestConfiguration;

    private ObjectProphecy $saferpayPaymentProcessor;

    private ObjectProphecy $router;

    private ObjectProphecy $logger;

    private MockClock $clock;

    private PrepareAssertAction $action;

    protected function setUp(): void
    {
        $this->requestConfigurationFactory = $this->prophesize(RequestConfigurationFactoryInterface::class);
        $this->orderMetadata = $this->prophesize(MetadataInterface::class);
        $this->paymentProvider = $this->prophesize(PaymentProviderInterface::class);
        $this->tokenProvider = $this->prophesize(TokenProviderInterface::class);
        $this->requestConfiguration = $this->prophesize(RequestConfiguration::class);
        $this->saferpayPaymentProcessor = $this->prophesize(SaferpayPaymentProcessorInterface::class);
        $this->router = $this->prophesize(UrlGeneratorInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->clock = new MockClock();

        $this->requestConfigurationFactory->create($this->orderMetadata, Argument::type(Request::class))->willReturn($this->requestConfiguration);

        $this->action = new PrepareAssertAction(
            $this->requestConfigurationFactory->reveal(),
            $this->orderMetadata->reveal(),
            $this->paymentProvider->reveal(),
            $this->tokenProvider->reveal(),
            $this->saferpayPaymentProcessor->reveal(),
            $this->router->reveal(),
            $this->logger->reveal(),
            $this->clock,
            1,
        );
    }

    public function test_it_waits_and_retries_while_the_payment_is_being_processed(): void
    {
        $request = $this->prophesize(Request::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $clock = new MockClock('2026-01-01 12:00:00', 'UTC');

        $action = new PrepareAssertAction(
            $this->requestConfigurationFactory->reveal(),
            $this->orderMetadata->reveal(),
            $this->paymentProvider->reveal(),
            $this->tokenProvider->reveal(),
            $this->saferpayPaymentProcessor->reveal(),
            $this->router->reveal(),
            $this->logger->reveal(),
            $clock,
            3,
            0.2,
        );

        $this->paymentProvider->provideForOrder('TOKEN')->willReturn($payment);
        $attempts = 0;
        $this->saferpayPaymentProcessor->lock($payment)->will(function () use (&$attempts): void {
            if (++$attempts < 3) {
                throw new PaymentBeingProcessedException();
            }
        });

        $this->paymentProvider->provideForAssert('TOKEN')->willReturn($payment);
        $this->tokenProvider->provideForAssert($payment, $this->requestConfiguration)->willReturn($token);
        $token->getTargetUrl()->willReturn('/url');

        self::assertEquals(new RedirectResponse('/url'), $action($request->reveal(), 'TOKEN'));
        self::assertSame(3, $attempts);
        self::assertEquals(new \DateTimeImmutable('2026-01-01 12:00:00.400', new \DateTimeZone('UTC')), $clock->now());
    }

    public function test_it_throws_an_exception_when_last_payment_for_given_order_token_value_does_not_exist(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $request = $this->prophesize(Request::class);

        $this->paymentProvider->provideForOrder('TOKEN')->willReturn($payment);
        $this->paymentProvider->provideForAssert('TOKEN')->willThrow(PaymentAlreadyProcessedException::class);

        $this->expectException(PaymentAlreadyProcessedException::class);

        ($this->action)($request->reveal(), 'TOKEN');
    }

    public function test_it_returns_to_thank_you_page_if_payment_is_already_processed(): void
    {
        $request = $this->prophesize(Request::class);
        $payment = $this->prophesize(PaymentInterface::class);

        $this->paymentProvider->provideForOrder('TOKEN')->willReturn($payment);
        $this->saferpayPaymentProcessor->lock($payment)->willThrow(PaymentAlreadyProcessedException::class);

        $this->router
            ->generate('commerce_weavers_sylius_after_unsuccessful_payment', ['tokenValue' => 'TOKEN'])
            ->willReturn('https://after-unsuccessful-payment.com/TOKEN')
        ;

        self::assertEquals(new RedirectResponse('https://after-unsuccessful-payment.com/TOKEN'), ($this->action)($request->reveal(), 'TOKEN'));
    }

    public function test_it_returns_to_thank_you_page_if_payment_is_being_processed(): void
    {
        $request = $this->prophesize(Request::class);
        $payment = $this->prophesize(PaymentInterface::class);

        $this->paymentProvider->provideForOrder('TOKEN')->willReturn($payment);
        $this->saferpayPaymentProcessor->lock($payment)->willThrow(PaymentBeingProcessedException::class);

        $this->router
            ->generate('commerce_weavers_sylius_after_unsuccessful_payment', ['tokenValue' => 'TOKEN'])
            ->willReturn('https://after-unsuccessful-payment.com/TOKEN')
        ;

        self::assertEquals(new RedirectResponse('https://after-unsuccessful-payment.com/TOKEN'), ($this->action)($request->reveal(), 'TOKEN'));
    }

    public function test_it_returns_redirect_response_to_target_url_from_token(): void
    {
        $request = $this->prophesize(Request::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $token = $this->prophesize(TokenInterface::class);

        $this->paymentProvider->provideForOrder('TOKEN')->willReturn($payment);
        $this->saferpayPaymentProcessor->lock($payment)->shouldBeCalled();

        $this->paymentProvider->provideForAssert('TOKEN')->willReturn($payment);
        $this->tokenProvider->provideForAssert($payment, $this->requestConfiguration)->willReturn($token);
        $token->getTargetUrl()->willReturn('/url');

        self::assertEquals(new RedirectResponse('/url'), ($this->action)($request->reveal(), 'TOKEN'));
    }
}
