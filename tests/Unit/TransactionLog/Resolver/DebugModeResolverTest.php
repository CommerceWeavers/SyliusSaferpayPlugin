<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\TransactionLog\Resolver;

use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Resolver\DebugModeResolver;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class DebugModeResolverTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_returns_true_when_debug_mode_is_set_to_true(): void
    {
        $this->assertTrue((new DebugModeResolver())->isEnabled($this->createPaymentWithGatewayConfig(['debug' => true])));
    }

    public function test_it_returns_false_when_debug_mode_is_set_to_false(): void
    {
        $this->assertFalse((new DebugModeResolver())->isEnabled($this->createPaymentWithGatewayConfig(['debug' => false])));
    }

    public function test_it_returns_false_when_debug_mode_is_not_set(): void
    {
        $this->assertFalse((new DebugModeResolver())->isEnabled($this->createPaymentWithGatewayConfig([])));
    }

    private function createPaymentWithGatewayConfig(array $config): PaymentInterface
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);

        $payment->getMethod()->willReturn($paymentMethod->reveal());
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());
        $gatewayConfig->getConfig()->willReturn($config);

        return $payment->reveal();
    }
}
