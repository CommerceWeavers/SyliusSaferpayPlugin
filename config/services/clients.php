<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClient;
use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientBodyFactory;
use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientBodyFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\UuidProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Resolver\SaferpayApiBaseUrlResolverInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services
        ->set(SaferpayClientInterface::class, SaferpayClient::class)
        ->public()
        ->args([
            service('sylius.http_client'),
            service(SaferpayClientBodyFactoryInterface::class),
            service(SaferpayApiBaseUrlResolverInterface::class),
        ])
    ;

    $services
        ->set(SaferpayClientBodyFactoryInterface::class, SaferpayClientBodyFactory::class)
        ->public()
        ->args([
            service(UuidProviderInterface::class),
        ])
    ;
};
