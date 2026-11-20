<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\Provider;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProvider;
use Payum\Core\Payum;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Bundle\ResourceBundle\Controller\Parameters;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class TokenProviderTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<Payum> */
    private ObjectProphecy $payum;

    /** @var ObjectProphecy<GenericTokenFactoryInterface> */
    private ObjectProphecy $tokenFactory;

    /** @var ObjectProphecy<PaymentInterface> */
    private ObjectProphecy $payment;

    /** @var ObjectProphecy<TokenInterface> */
    private ObjectProphecy $token;

    private TokenProvider $tokenProvider;

    protected function setUp(): void
    {
        $this->payum = $this->prophesize(Payum::class);
        $this->tokenFactory = $this->prophesize(GenericTokenFactoryInterface::class);
        $this->payment = $this->prophesize(PaymentInterface::class);
        $this->token = $this->prophesize(TokenInterface::class);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);

        $this->payment->getMethod()->willReturn($paymentMethod->reveal());
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());
        $gatewayConfig->getGatewayName()->willReturn('saferpay');

        $this->payum->getTokenFactory()->willReturn($this->tokenFactory->reveal());

        $this->tokenProvider = new TokenProvider($this->payum->reveal());
    }

    public function test_it_provides_token_for_assert_with_route_as_string(): void
    {
        $requestConfiguration = $this->createRequestConfiguration('sylius_shop_order_thank_you');

        $this->tokenFactory
            ->createToken('saferpay', $this->payment->reveal(), 'sylius_shop_order_thank_you', [])
            ->willReturn($this->token->reveal())
        ;

        self::assertSame(
            $this->token->reveal(),
            $this->tokenProvider->provideForAssert($this->payment->reveal(), $requestConfiguration),
        );
    }

    public function test_it_provides_token_for_assert_with_route_as_array(): void
    {
        $requestConfiguration = $this->createRequestConfiguration([
            'route' => 'sylius_shop_order_thank_you',
            'parameters' => ['abc' => 'def'],
        ]);

        $this->tokenFactory
            ->createToken('saferpay', $this->payment->reveal(), 'sylius_shop_order_thank_you', ['abc' => 'def'])
            ->willReturn($this->token->reveal())
        ;

        self::assertSame(
            $this->token->reveal(),
            $this->tokenProvider->provideForAssert($this->payment->reveal(), $requestConfiguration),
        );
    }

    public function test_it_provides_token_for_command_handler(): void
    {
        $this->tokenFactory
            ->createToken('saferpay', $this->payment->reveal(), 'sylius_shop_homepage')
            ->willReturn($this->token->reveal())
        ;

        self::assertSame(
            $this->token->reveal(),
            $this->tokenProvider->provideForCommandHandler($this->payment->reveal()),
        );
    }

    public function test_it_provides_token_for_capture_with_route_as_string(): void
    {
        $requestConfiguration = $this->createRequestConfiguration('sylius_shop_order_thank_you');

        $this->tokenFactory
            ->createCaptureToken('saferpay', $this->payment->reveal(), 'sylius_shop_order_thank_you', [])
            ->willReturn($this->token->reveal())
        ;

        self::assertSame(
            $this->token->reveal(),
            $this->tokenProvider->provideForCapture($this->payment->reveal(), $requestConfiguration),
        );
    }

    public function test_it_provides_token_for_capture_with_route_as_array(): void
    {
        $requestConfiguration = $this->createRequestConfiguration([
            'route' => 'sylius_shop_order_thank_you',
            'parameters' => ['abc' => 'def'],
        ]);

        $this->tokenFactory
            ->createCaptureToken('saferpay', $this->payment->reveal(), 'sylius_shop_order_thank_you', ['abc' => 'def'])
            ->willReturn($this->token->reveal())
        ;

        self::assertSame(
            $this->token->reveal(),
            $this->tokenProvider->provideForCapture($this->payment->reveal(), $requestConfiguration),
        );
    }

    public function test_it_provides_token_for_webhook(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $this->payment->getOrder()->willReturn($order->reveal());
        $order->getTokenValue()->willReturn('TOKEN');

        $this->tokenFactory
            ->createToken(
                'saferpay',
                $this->payment->reveal(),
                'commerce_weavers_sylius_saferpay_webhook',
                ['order_token' => 'TOKEN'],
            )
            ->willReturn($this->token->reveal())
        ;

        self::assertSame(
            $this->token->reveal(),
            $this->tokenProvider->provideForWebhook($this->payment->reveal(), 'commerce_weavers_sylius_saferpay_webhook'),
        );
    }

    public function test_it_provides_token(): void
    {
        $this->tokenFactory
            ->createToken('saferpay', $this->payment->reveal(), 'sylius_admin_order_show', ['id' => '1'])
            ->willReturn($this->token->reveal())
        ;

        self::assertSame(
            $this->token->reveal(),
            $this->tokenProvider->provide($this->payment->reveal(), 'sylius_admin_order_show', ['id' => '1']),
        );
    }

    private function createRequestConfiguration(string|array $redirect): RequestConfiguration
    {
        $requestConfiguration = $this->prophesize(RequestConfiguration::class);
        $parameters = $this->prophesize(Parameters::class);

        $requestConfiguration->getParameters()->willReturn($parameters->reveal());
        $parameters->get('redirect')->willReturn($redirect);

        return $requestConfiguration->reveal();
    }
}
