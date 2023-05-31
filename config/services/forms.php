<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Form\Type\SaferpayGatewayConfigurationType;
use CommerceWeavers\SyliusSaferpayPlugin\Form\Type\SaferpayPaymentMethodsConfigurationType;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\SaferpayPaymentMethodsProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services
        ->set(SaferpayGatewayConfigurationType::class)
        ->tag('sylius.gateway_configuration_type', ['type' => 'saferpay', 'label' => 'commerce_weavers_saferpay.ui.saferpay'])
        ->tag('form.type')
    ;

    $services
        ->set(SaferpayPaymentMethodsConfigurationType::class)
        ->args([
            service(SaferpayPaymentMethodsProviderInterface::class),
        ])
        ->tag('form.type')
    ;
};
