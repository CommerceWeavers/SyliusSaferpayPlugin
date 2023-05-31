<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Provider;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use Payum\Core\Model\GatewayConfigInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class SaferpayPaymentMethodsProviderSpec extends ObjectBehavior
{
    function let(SaferpayClientInterface $client): void
    {
        $this->beConstructedWith($client);
    }

    function it_throws_an_exception_when_the_payment_method_has_no_gateway_config(
        PaymentMethodInterface $paymentMethod,
    ): void {
        $paymentMethod->getGatewayConfig()->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('provide', [$paymentMethod])
        ;
    }

    function it_provides_an_array_with_payment_methods_from_saferpay_api(
        SaferpayClientInterface $client,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $client->getTerminal($gatewayConfig)->willReturn([
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

        $this->provide($paymentMethod)->shouldReturn(['TWINT', 'VISA']);
        ;
    }
}
