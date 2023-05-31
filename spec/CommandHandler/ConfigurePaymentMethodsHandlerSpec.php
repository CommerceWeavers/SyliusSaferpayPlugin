<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\CommandHandler;

use CommerceWeavers\SyliusSaferpayPlugin\Command\ConfigurePaymentMethods;
use Payum\Core\Model\GatewayConfigInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;

final class ConfigurePaymentMethodsHandlerSpec extends ObjectBehavior
{
    function let(PaymentMethodRepositoryInterface $paymentMethodRepository): void
    {
        $this->beConstructedWith($paymentMethodRepository);
    }

    public function it_updates_the_allowed_payment_methods_config_of_a_given_payment_method(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $paymentMethodRepository->find('payment_method_id')->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([]);

        $gatewayConfig->setConfig(['allowed_payment_methods' => ['1', '1']])->shouldBeCalled();

        $this(new ConfigurePaymentMethods('payment_method_id', ['1', '1']));
    }

    function it_throws_an_exception_when_the_payment_method_cannot_be_found(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
    ): void {
        $paymentMethodRepository->find('payment_method_id')->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [new ConfigurePaymentMethods('payment_method_id', ['1', '1'])])
        ;
    }

    function it_throws_an_exception_when_the_payment_method_has_no_gateway_config(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodInterface $paymentMethod,
    ): void {
        $paymentMethodRepository->find('payment_method_id')->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [new ConfigurePaymentMethods('payment_method_id', ['1', '1'])])
        ;
    }
}
