<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\CommandHandler;

use CommerceWeavers\SyliusSaferpayPlugin\Command\ConfigurePaymentMethods;
use CommerceWeavers\SyliusSaferpayPlugin\CommandHandler\ConfigurePaymentMethodsHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;

final class ConfigurePaymentMethodsHandlerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $paymentMethodRepository;

    private ConfigurePaymentMethodsHandler $handler;

    protected function setUp(): void
    {
        $this->paymentMethodRepository = $this->prophesize(PaymentMethodRepositoryInterface::class);

        $this->handler = new ConfigurePaymentMethodsHandler($this->paymentMethodRepository->reveal());
    }

    public function test_it_updates_the_allowed_payment_methods_config_of_a_given_payment_method(): void
    {
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);

        $this->paymentMethodRepository->find('payment_method_id')->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([]);

        $gatewayConfig->setConfig(['allowed_payment_methods' => ['1', '1']])->shouldBeCalled();

        ($this->handler)(new ConfigurePaymentMethods('payment_method_id', ['1', '1']));
    }

    public function test_it_throws_an_exception_when_the_payment_method_cannot_be_found(): void
    {
        $this->paymentMethodRepository->find('payment_method_id')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        ($this->handler)(new ConfigurePaymentMethods('payment_method_id', ['1', '1']));
    }

    public function test_it_throws_an_exception_when_the_payment_method_has_no_gateway_config(): void
    {
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);

        $this->paymentMethodRepository->find('payment_method_id')->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        ($this->handler)(new ConfigurePaymentMethods('payment_method_id', ['1', '1']));
    }
}
