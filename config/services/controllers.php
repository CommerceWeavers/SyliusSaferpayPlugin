<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Controller\ResourceController;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services
        ->set(ResourceController::class)
        ->decorate('sylius.controller.payment')
        ->args([
            service('.inner'),
            inline_service(MetadataInterface::class)
                ->factory([service('sylius.resource_registry'), 'get'])
                ->args(['sylius.payment']),
            service('sylius.resource_controller.request_configuration_factory'),
            service('sylius.resource_controller.redirect_handler'),
        ])
    ;
};
