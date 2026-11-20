<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Provider;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\SaferpayPaymentMethodsProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class SaferpayPaymentMethodsProviderTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<SaferpayClientInterface> */
    private ObjectProphecy $client;

    private SaferpayPaymentMethodsProvider $provider;

    protected function setUp(): void
    {
        $this->client = $this->prophesize(SaferpayClientInterface::class);

        $this->provider = new SaferpayPaymentMethodsProvider($this->client->reveal());
    }

    public function test_it_throws_an_exception_when_the_payment_method_has_no_gateway_config(): void
    {
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        $this->provider->provide($paymentMethod->reveal());
    }

    public function test_it_provides_an_array_with_payment_methods_from_saferpay_api(): void
    {
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);

        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());
        $this->client->getTerminal($gatewayConfig->reveal())->willReturn([
            'TerminalId' => '17757531',
            'Type' => 'ECOM',
            'PaymentMethods' => [[
                'PaymentMethod' => 'TWINT',
                'Currencies' => ['CHF'],
            ], [
                'PaymentMethod' => 'VISA',
                'Currencies' => ['EUR', 'CHF', 'USD'],
            ]],
        ]);

        $this->assertSame(['TWINT', 'VISA'], $this->provider->provide($paymentMethod->reveal()));
    }

    public function test_it_provides_an_empty_array_if_no_payment_methods_returned_from_api(): void
    {
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);

        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());
        $this->client->getTerminal($gatewayConfig->reveal())->willReturn([
            'Behavior' => 'DO_NOT_RETRY',
            'ErrorName' => 'VALIDATION_FAILED',
            'ErrorMessage' => 'invalid customerId',
            'StatusCode' => '400',
        ]);

        $this->assertSame([], $this->provider->provide($paymentMethod->reveal()));
    }
}
