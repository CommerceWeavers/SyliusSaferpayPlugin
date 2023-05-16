<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\AssertFactory;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\AssertFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\RefundFactory;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\RefundFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\ResolveNextCommandFactory;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\ResolveNextCommandFactoryInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set(AssertFactoryInterface::class, AssertFactory::class);

    $services->set(RefundFactoryInterface::class, RefundFactory::class);

    $services->set(ResolveNextCommandFactoryInterface::class, ResolveNextCommandFactory::class);
};
