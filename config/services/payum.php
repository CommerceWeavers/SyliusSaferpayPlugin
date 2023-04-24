<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\AssertAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\AuthorizeAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\CaptureAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\ResolveNextRouteAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\SaferpayGatewayFactory;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StateMarker;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StateMarkerInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusChecker;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusCheckerInterface;
use Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services
        ->set(SaferpayGatewayFactory::class, GatewayFactoryBuilder::class)
        ->args([
            SaferpayGatewayFactory::class,
        ])
        ->tag('payum.gateway_factory_builder', ['factory' => 'saferpay'])
    ;

    $services
        ->set(AuthorizeAction::class)
        ->public()
        ->args([
            service(SaferpayClientInterface::class)
        ])
        ->tag('payum.action', ['factory' => 'saferpay', 'alias' => 'payum.action.authorize'])
    ;

    $services
        ->set(AssertAction::class)
        ->public()
        ->args([
            service(SaferpayClientInterface::class),
        ])
        ->tag('payum.action', ['factory' => 'saferpay', 'alias' => 'payum.action.assert'])
    ;

    $services
        ->set(CaptureAction::class)
        ->public()
        ->args([
            service(SaferpayClientInterface::class),
            service(StatusCheckerInterface::class),
        ])
        ->tag('payum.action', ['factory' => 'saferpay', 'alias' => 'payum.action.capture'])
    ;

    $services
        ->set(ResolveNextRouteAction::class)
        ->public()
        ->args([
            service(StatusCheckerInterface::class),
        ])
        ->tag('payum.action', ['factory' => 'saferpay', 'alias' => 'payum.action.resolve_next_route'])
    ;

    $services
        ->set(StatusAction::class)
        ->public()
        ->args([
            service(StateMarkerInterface::class),
        ])
        ->tag('payum.action', ['factory' => 'saferpay', 'alias' => 'payum.action.status'])
    ;

    $services
        ->set(StateMarkerInterface::class, StateMarker::class)
        ->args([
            service(StatusCheckerInterface::class),
        ])
    ;

    $services
        ->set(StatusCheckerInterface::class, StatusChecker::class)
    ;
};
