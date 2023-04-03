<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('commerce_weavers_sylius_saferpay');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
