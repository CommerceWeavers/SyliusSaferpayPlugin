<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $gatewayConfiguration = [
        'saferpay' => [
            'template' => '@CommerceWeaversSyliusSaferpayPlugin/admin/payment_method/form/sections/gateway_configuration/saferpay.html.twig',
        ],
    ];

    $containerConfigurator->extension('sylius_twig_hooks', [
        'hooks' => [
            'sylius_admin.payment_method.create.content.form.sections.gateway_configuration.saferpay' => $gatewayConfiguration,
            'sylius_admin.payment_method.update.content.form.sections.gateway_configuration.saferpay' => $gatewayConfiguration,
        ],
    ]);
};
