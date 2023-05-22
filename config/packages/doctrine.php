<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->extension('doctrine', [
        'orm' => [
            'entity_managers' => [
                'default' => [
                    'mappings' => [
                        'transaction_log' => [
                            'mapping' => true,
                            'type' => 'xml',
                            'dir' => '%kernel.project_dir%/vendor/commerce-weavers/sylius-saferpay-plugin/config/doctrine',
                            'alias' => 'transaction_log',
                            'prefix' => 'CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Entity',
                            'is_bundle' => false,
                        ],
                    ],
                ],
            ],
        ],
    ]);
};
