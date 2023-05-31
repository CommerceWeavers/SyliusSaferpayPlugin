<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\CommandHandler;

use CommerceWeavers\SyliusSaferpayPlugin\Command\ConfigurePaymentMethods;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Webmozart\Assert\Assert;

final class ConfigurePaymentMethodsHandler
{
    public function __construct(private PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
    }

    public function __invoke(ConfigurePaymentMethods $command): void
    {
        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $this->paymentMethodRepository->find($command->getPaymentMethodId());
        Assert::notNull($paymentMethod);

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);

        $configuration = $gatewayConfig->getConfig();
        $configuration['allowed_payment_methods'] = $command->getPaymentMethods();
        $gatewayConfig->setConfig($configuration);
    }
}
