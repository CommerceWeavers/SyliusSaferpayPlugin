<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\CommandHandler\ConfigurePaymentMethodsHandler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services
        ->set(ConfigurePaymentMethodsHandler::class)
        ->args([
            service('sylius.repository.payment_method'),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.command_bus'])
    ;
};
