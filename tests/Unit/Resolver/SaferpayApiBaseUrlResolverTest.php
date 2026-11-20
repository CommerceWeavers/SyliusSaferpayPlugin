<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Resolver;

use CommerceWeavers\SyliusSaferpayPlugin\Resolver\SaferpayApiBaseUrlResolver;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Payment\Model\GatewayConfigInterface;

final class SaferpayApiBaseUrlResolverTest extends TestCase
{
    use ProphecyTrait;

    private SaferpayApiBaseUrlResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new SaferpayApiBaseUrlResolver('https://www.saferpay.com/api/', 'https://test.saferpay.com/api/');
    }

    public function test_it_returns_the_api_base_url_if_sandbox_is_false(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['sandbox' => false]);

        $this->assertSame('https://www.saferpay.com/api/', $this->resolver->resolve($gatewayConfig->reveal()));
    }

    public function test_it_returns_the_test_api_base_url_if_sandbox_is_true(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['sandbox' => true]);

        $this->assertSame('https://test.saferpay.com/api/', $this->resolver->resolve($gatewayConfig->reveal()));
    }
}
