<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\PaymentAssertionFailureListener;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\PaymentAssertionSuccessListener;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\PaymentAuthorizationSuccessListener;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\PaymentCaptureSuccessListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set(PaymentAssertionFailureListener::class)
        ->args([
            service('commerce_weavers_saferpay.factory.transaction_log'),
            service('commerce_weavers_saferpay.manager.transaction_log'),
            service('sylius.repository.payment'),
            service('sylius.calendar'),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.event_bus'])
    ;

    $services->set(PaymentAssertionSuccessListener::class)
        ->args([
            service('commerce_weavers_saferpay.factory.transaction_log'),
            service('commerce_weavers_saferpay.manager.transaction_log'),
            service('sylius.repository.payment'),
            service('sylius.calendar'),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.event_bus'])
    ;

    $services->set(PaymentAuthorizationSuccessListener::class)
        ->args([
            service('commerce_weavers_saferpay.factory.transaction_log'),
            service('commerce_weavers_saferpay.manager.transaction_log'),
            service('sylius.repository.payment'),
            service('sylius.calendar'),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.event_bus'])
    ;

    $services->set(PaymentCaptureSuccessListener::class)
        ->args([
            service('commerce_weavers_saferpay.factory.transaction_log'),
            service('commerce_weavers_saferpay.manager.transaction_log'),
            service('sylius.repository.payment'),
            service('sylius.calendar'),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.event_bus'])
    ;
};
