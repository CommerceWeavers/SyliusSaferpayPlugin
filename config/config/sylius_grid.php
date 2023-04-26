<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->extension('sylius_grid', [
        'grids' => [
            'commerce_weavers_saferpay_transaction_log' => [
                'driver' => [
                    'name' => 'doctrine/orm',
                    'options' => [
                        'class' => '%commerce_weavers.model.transaction_log.class%',
                    ],
                ],
                'fields' => [
                    'occurredAt' => [
                        'type' => 'datetime',
                        'label' => 'commerce_weavers_saferpay.ui.occurred_at',
                        'sortable' => true,
                        'options' => [
                            'format' => 'Y-m-d H:i:s',
                        ],
                    ],
                    'orderNumber' => [
                        'type' => 'string',
                        'label' => 'sylius.ui.order',
                        'sortable' => true,
                        'path' => 'payment.order.number',
                    ],
                    'description' => [
                        'type' => 'string',
                        'label' => 'sylius.ui.description',
                    ],
                    'type' => [
                        'type' => 'string',
                        'label' => 'sylius.ui.type',
                    ]
                ],
                'filters' => [
                    'occurredAt' => [
                        'type' => 'date',
                        'label' => 'commerce_weavers_saferpay.ui.occurred_at',
                    ],
                ],
            ],
        ],
    ]);
};
