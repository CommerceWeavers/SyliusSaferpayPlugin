<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\CommandHandler\ConfigurePaymentMethodsHandler;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\Handler\AssertPaymentHandler;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\Handler\CapturePaymentHandler;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\AssertFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\CaptureFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\ResolveNextCommandFactoryInterface;
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

    $services
        ->set(AssertPaymentHandler::class)
        ->args([
            service('sylius.command_bus'),
            service('payum'),
            service('payum.security.token_storage'),
            service(AssertFactoryInterface::class),
            service('sylius.factory.payum_get_status_action'),
            service(ResolveNextCommandFactoryInterface::class),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.command_bus'])
    ;

    $services
        ->set(CapturePaymentHandler::class)
        ->args([
            service('sylius.command_bus'),
            service('payum'),
            service('payum.security.token_storage'),
            service(CaptureFactoryInterface::class),
            service('sylius.factory.payum_get_status_action'),
            service(ResolveNextCommandFactoryInterface::class),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.command_bus'])
    ;
};
