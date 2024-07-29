<?php

declare(strict_types=1);

use Sylius\Bundle\CoreBundle\Application\Kernel;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

if (Kernel::VERSION_ID < 11300) {
    return;
}

return static function (ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->extension('framework', [
        'uid' => [
            'default_uuid_version' => 6,
            'time_based_uuid_version' => 6,
        ],
    ]);
};
