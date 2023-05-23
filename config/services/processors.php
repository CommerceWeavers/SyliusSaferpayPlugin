<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\RefundFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Processor\RefundProcessor;
use CommerceWeavers\SyliusSaferpayPlugin\Processor\RefundProcessorInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services
        ->set(RefundProcessorInterface::class, RefundProcessor::class)
        ->public()
        ->args([
            service(TokenProviderInterface::class),
            service('payum'),
            service('sylius.factory.payum_get_status_action'),
            service(RefundFactoryInterface::class),
        ])
    ;
};
