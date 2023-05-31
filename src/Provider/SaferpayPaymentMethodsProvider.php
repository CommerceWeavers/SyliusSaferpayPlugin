<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Provider;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Webmozart\Assert\Assert;

final class SaferpayPaymentMethodsProvider implements SaferpayPaymentMethodsProviderInterface
{
    public function __construct(private SaferpayClientInterface $client)
    {
    }

    public function provide(PaymentMethodInterface $paymentMethod): array
    {
        /** @var GatewayConfigInterface|null $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);

        $terminal = $this->client->getTerminal($gatewayConfig);

        return array_map(
            fn (array $paymentMethodData) => $paymentMethodData['PaymentMethod'],
            $terminal['PaymentMethods'],
        );
    }
}
