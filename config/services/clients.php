<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClient;
use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientBodyFactory;
use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientBodyFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\EventDispatcher\PaymentEventDispatcherInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\UuidProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Resolver\SaferpayApiBaseUrlResolverInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Routing\Generator\WebhookRouteGeneratorInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services
        ->set(SaferpayClientInterface::class, SaferpayClient::class)
        ->public()
        ->args([
            service('http_client'),
            service(SaferpayClientBodyFactoryInterface::class),
            service(SaferpayApiBaseUrlResolverInterface::class),
            service(PaymentEventDispatcherInterface::class),
            service('monolog.logger.saferpay'),
        ])
    ;

    $services
        ->set(SaferpayClientBodyFactoryInterface::class, SaferpayClientBodyFactory::class)
        ->public()
        ->args([
            service(UuidProviderInterface::class),
            service(TokenProviderInterface::class),
            service(WebhookRouteGeneratorInterface::class),
        ])
    ;
};
