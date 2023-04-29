<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\DependencyInjection;

use CommerceWeavers\SyliusSaferpayPlugin\Entity\TransactionLog;
use CommerceWeavers\SyliusSaferpayPlugin\Entity\TransactionLogInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Factory\TransactionLogFactory;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('commerce_weavers_sylius_saferpay');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('driver')->defaultValue(SyliusResourceBundle::DRIVER_DOCTRINE_ORM)->end()
                ->scalarNode('api_base_url')->defaultValue('https://saferpay.com/api/')->end()
                ->scalarNode('test_api_base_url')->defaultValue('https://test.saferpay.com/api/')->end()
            ->end()
        ;

        $this->addResourcesSection($rootNode);

        return $treeBuilder;
    }

    private function addResourcesSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('transaction_log')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(TransactionLog::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(TransactionLogInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(EntityRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(TransactionLogFactory::class)->end()
                                        ->scalarNode('form')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ;
    }
}
