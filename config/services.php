<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Processor\SaferpayPaymentProcessor;
use CommerceWeavers\SyliusSaferpayPlugin\Processor\SaferpayPaymentProcessorInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Resolver\SaferpayApiBaseUrlResolver;
use CommerceWeavers\SyliusSaferpayPlugin\Resolver\SaferpayApiBaseUrlResolverInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(__DIR__ . '/services/**/**');

    $services = $containerConfigurator->services();
    $services
        ->set(SaferpayApiBaseUrlResolverInterface::class, SaferpayApiBaseUrlResolver::class)
        ->public()
        ->args([
            param('commerce_weavers.saferpay.api_base_url'),
            param('commerce_weavers.saferpay.test_api_base_url'),
        ])
    ;

    $services
        ->set(SaferpayPaymentProcessorInterface::class, SaferpayPaymentProcessor::class)
        ->public()
        ->args([
            service('lock.factory'),
            service('doctrine.orm.entity_manager'),
            service('monolog.logger.saferpay'),
        ])
    ;
};
