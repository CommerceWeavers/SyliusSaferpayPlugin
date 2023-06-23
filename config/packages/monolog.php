<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->extension('monolog', [
        'channels' => ['saferpay'],
        'handlers' => [
            'main' => [
                'type' => 'stream',
                'path' => '%kernel.logs_dir%/%kernel.environment%.log',
                'level' => 'debug',
                'channels' => ['!event', '!doctrine'],
            ],
            'saferpay' => [
                'type' => 'stream',
                'path' => '%kernel.logs_dir%/saferpay.log',
                'level' => 'debug',
                'channels' => ['saferpay'],
            ],
        ],
    ]);
};
