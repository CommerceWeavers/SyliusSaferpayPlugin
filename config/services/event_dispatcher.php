<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Payment\EventDispatcher\PaymentEventDispatcher;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\EventDispatcher\PaymentEventDispatcherInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services
        ->set(PaymentEventDispatcherInterface::class, PaymentEventDispatcher::class)
        ->public()
        ->args([
            service('sylius.event_bus'),
        ])
    ;
};
