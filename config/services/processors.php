<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Payment\EventListener\RefundPaymentListener;
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
            service('sylius_payum.factory.get_status'),
            service(RefundFactoryInterface::class),
        ])
    ;

    $services
        ->set(RefundPaymentListener::class)
        ->args([service(RefundProcessorInterface::class)])
        ->tag('kernel.event_listener', ['event' => 'workflow.sylius_payment.transition.refund', 'priority' => -100])
    ;
};
