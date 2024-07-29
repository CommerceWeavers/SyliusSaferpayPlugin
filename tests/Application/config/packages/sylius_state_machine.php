<?php

declare(strict_types=1);

use Sylius\Bundle\CoreBundle\Application\Kernel;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

if (Kernel::VERSION_ID < 11300) {
    return;
}

return static function (ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->extension('sylius_state_machine_abstraction', [
        'default_adapter' => 'winzou_state_machine',
    ]);
};
