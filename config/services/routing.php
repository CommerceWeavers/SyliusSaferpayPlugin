<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Routing\Generator\WebhookRouteGeneratorInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Routing\Generator\WebhookUrlGenerator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services
        ->set(WebhookRouteGeneratorInterface::class, WebhookUrlGenerator::class)
        ->args([
            service('router'),
        ])
    ;
};
