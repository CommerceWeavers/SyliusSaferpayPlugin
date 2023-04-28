<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\DependencyInjection;

use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class CommerceWeaversSyliusSaferpayExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    private const ALIAS = 'commerce_weavers_saferpay';

    /** @psalm-suppress UnusedVariable */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $container->setParameter('commerce_weavers.saferpay.api_base_url', $config['api_base_url']);
        $container->setParameter('commerce_weavers.saferpay.test_api_base_url', $config['test_api_base_url']);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $config = $this->getCurrentConfiguration($container);
        $this->registerResources(self::ALIAS, $config['driver'], $config['resources'], $container);

        $this->prependDoctrineMigrations($container);
        $this->prependDoctrineMappings($container);
    }

    private function getCurrentConfiguration(ContainerBuilder $container): array
    {
        /** @var ConfigurationInterface $configuration */
        $configuration = $this->getConfiguration([], $container);

        $configs = $container->getExtensionConfig($this->getAlias());

        return $this->processConfiguration($configuration, $configs);
    }

    private function prependDoctrineMappings(ContainerBuilder $container): void
    {
        /** @var array<string, array<string, string>> $metadata */
        $metadata = $container->getParameter('kernel.bundles_metadata');

        $config = array_merge(...$container->getExtensionConfig('doctrine'));

        if (!isset($config['dbal']) || !isset($config['orm'])) {
            return;
        }

        $rootPathToReturnPlugin = $metadata['CommerceWeaversSyliusSaferpayPlugin']['path'];

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    'CommerceWeaversSyliusSaferpayPlugin' => [
                        'type' => 'xml',
                        'dir' => $rootPathToReturnPlugin . '/config/doctrine/',
                        'is_bundle' => false,
                        'prefix' => 'CommerceWeavers\SyliusSaferpayPlugin\Entity',
                        'alias' => 'CommerceWeaversSyliusSaferpay',
                    ],
                ],
            ],
        ]);
    }

    protected function getMigrationsNamespace(): string
    {
        return 'DoctrineMigrations';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@CommerceWeaversSyliusSaferpayPlugin/migrations';
    }

    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return [
            'Sylius\Bundle\CoreBundle\Migrations',
        ];
    }
}
