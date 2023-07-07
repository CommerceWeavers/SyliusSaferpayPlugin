<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Resolver;

use Payum\Core\Model\GatewayConfigInterface;
use PhpSpec\ObjectBehavior;

final class SaferpayApiBaseUrlResolverSpec extends ObjectBehavior
{
    function let(): void
    {
        $this->beConstructedWith('https://www.saferpay.com/api/', 'https://test.saferpay.com/api/');
    }

    function it_returns_the_api_base_url_if_sandbox_is_false(GatewayConfigInterface $gatewayConfig): void
    {
        $gatewayConfig->getConfig()->willReturn(['sandbox' => false]);

        $this->resolve($gatewayConfig)->shouldReturn('https://www.saferpay.com/api/');
    }

    function it_returns_the_test_api_base_url_if_sandbox_is_true(GatewayConfigInterface $gatewayConfig): void
    {
        $gatewayConfig->getConfig()->willReturn(['sandbox' => true]);

        $this->resolve($gatewayConfig)->shouldReturn('https://test.saferpay.com/api/');
    }
}
