<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\AssertAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\AuthorizeAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\CaptureAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\ResolveNextRouteAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\SaferpayGatewayFactory;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\UuidProvider;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\UuidProviderInterface;
use Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set(UuidProviderInterface::class, UuidProvider::class);
};
