<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Form\Type\SaferpayGatewayConfigurationType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services
        ->set(SaferpayGatewayConfigurationType::class)
        ->tag('sylius.gateway_configuration_type', ['type' => 'saferpay', 'label' => 'sylius_saferpay.saferpay'])
        ->tag('form.type')
    ;
};
