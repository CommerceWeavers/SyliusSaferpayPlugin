<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\PaymentAssertionFailureListener;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\PaymentAssertionSuccessListener;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\PaymentAuthorizationFailureListener;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\PaymentAuthorizationSuccessListener;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\PaymentCaptureFailureListener;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\PaymentCaptureSuccessListener;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\PaymentRefundSuccessListener;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Resolver\DebugModeResolverInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set(PaymentAssertionFailureListener::class)
        ->args([
            service('commerce_weavers_saferpay.factory.transaction_log'),
            service('commerce_weavers_saferpay.manager.transaction_log'),
            service('sylius.repository.payment'),
            service(ClockInterface::class),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.event_bus'])
    ;

    $services->set(PaymentAssertionSuccessListener::class)
        ->args([
            service('commerce_weavers_saferpay.factory.transaction_log'),
            service('commerce_weavers_saferpay.manager.transaction_log'),
            service('sylius.repository.payment'),
            service(ClockInterface::class),
            service(DebugModeResolverInterface::class),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.event_bus'])
    ;

    $services->set(PaymentAuthorizationFailureListener::class)
        ->args([
            service('commerce_weavers_saferpay.factory.transaction_log'),
            service('commerce_weavers_saferpay.manager.transaction_log'),
            service('sylius.repository.payment'),
            service(ClockInterface::class),
            service(DebugModeResolverInterface::class),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.event_bus'])
    ;

    $services->set(PaymentAuthorizationSuccessListener::class)
        ->args([
            service('commerce_weavers_saferpay.factory.transaction_log'),
            service('commerce_weavers_saferpay.manager.transaction_log'),
            service('sylius.repository.payment'),
            service(ClockInterface::class),
            service(DebugModeResolverInterface::class),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.event_bus'])
    ;

    $services->set(PaymentCaptureFailureListener::class)
        ->args([
            service('commerce_weavers_saferpay.factory.transaction_log'),
            service('commerce_weavers_saferpay.manager.transaction_log'),
            service('sylius.repository.payment'),
            service(ClockInterface::class),
            service(DebugModeResolverInterface::class),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.event_bus'])
    ;

    $services->set(PaymentCaptureSuccessListener::class)
        ->args([
            service('commerce_weavers_saferpay.factory.transaction_log'),
            service('commerce_weavers_saferpay.manager.transaction_log'),
            service('sylius.repository.payment'),
            service(ClockInterface::class),
            service(DebugModeResolverInterface::class),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.event_bus'])
    ;

    $services->set(PaymentRefundSuccessListener::class)
        ->args([
            service('commerce_weavers_saferpay.factory.transaction_log'),
            service('commerce_weavers_saferpay.manager.transaction_log'),
            service('sylius.repository.payment'),
            service(ClockInterface::class),
            service(DebugModeResolverInterface::class),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.event_bus'])
    ;
};
