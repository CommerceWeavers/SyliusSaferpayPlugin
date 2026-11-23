<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    // doctrine/doctrine-bundle 3.x no longer generates proxy classes; Sylius removed `auto_generate_proxy_classes`
    // from its configuration and asks applications still on doctrine-bundle 2.x to set it (see Sylius UPGRADE-2.3.md).
    // `use_savepoints` is intentionally not enabled: with DBAL 3 and older doctrine/migrations it breaks
    // Sylius' MySQL migrations ("SAVEPOINT DOCTRINE_x does not exist"); DBAL 4 always uses savepoints anyway.
    if (version_compare((string) InstalledVersions::getVersion('doctrine/doctrine-bundle'), '3.0.0', '>=')) {
        return;
    }

    $container->extension('doctrine', [
        'orm' => [
            'auto_generate_proxy_classes' => !\in_array($container->env(), ['prod', 'test_cached'], true),
        ],
    ]);
};
