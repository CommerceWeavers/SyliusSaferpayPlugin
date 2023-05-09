<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Form\Type\SaferpayGatewayConfigurationType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services
        ->set(SaferpayGatewayConfigurationType::class)
        ->tag('sylius.gateway_configuration_type', ['type' => 'saferpay', 'label' => 'commerce_weavers_saferpay.ui.saferpay'])
        ->tag('form.type')
    ;
};
