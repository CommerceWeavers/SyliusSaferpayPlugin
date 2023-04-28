<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Event\Handler\SaferpayPaymentEventHandler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services
        ->set(SaferpayPaymentEventHandler::class)
        ->args([
            service('commerce_weavers_saferpay.repository.transaction_log'),
            service('sylius.repository.payment'),
            service('commerce_weavers_saferpay.manager.transaction_log'),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.event_bus'])
    ;
};
