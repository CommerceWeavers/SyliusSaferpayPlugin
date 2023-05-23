<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Resolver\DebugModeResolver;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Resolver\DebugModeResolverInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set(DebugModeResolverInterface::class, DebugModeResolver::class);
};
