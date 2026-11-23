<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $container) {
    // winzou/state-machine-bundle is optional on Sylius 2; the Symfony Workflow adapter is covered by RefundPaymentListener
    if (!$container->hasExtension('winzou_state_machine')) {
        return;
    }

    $containerConfigurator->extension('winzou_state_machine', [
        'sylius_payment' => [
            'callbacks' => [
                'before' => [
                    'refund_saferpay_payment' => [
                        'on' => ['refund'],
                        'do' => ['@CommerceWeavers\SyliusSaferpayPlugin\Processor\RefundProcessorInterface', 'process'],
                        'args' => ['object'],
                        'priority' => -100,
                    ],
                ],
            ],
        ],
    ]);
};
