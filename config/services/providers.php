<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\OrderProvider;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\OrderProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\PaymentProvider;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\PaymentProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\SaferpayPaymentMethodsProvider;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\SaferpayPaymentMethodsProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\UuidProvider;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\UuidProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services
        ->set(OrderProviderInterface::class, OrderProvider::class)
        ->public()
        ->args([
            service('sylius.repository.order'),
        ])
    ;

    $services
        ->set(PaymentProviderInterface::class, PaymentProvider::class)
        ->public()
        ->args([
            service(OrderProviderInterface::class),
        ])
    ;

    $services->set(SaferpayPaymentMethodsProviderInterface::class, SaferpayPaymentMethodsProvider::class)
        ->public()
        ->args([
            service(SaferpayClientInterface::class),
        ]);

    $services->set(UuidProviderInterface::class, UuidProvider::class);
};
