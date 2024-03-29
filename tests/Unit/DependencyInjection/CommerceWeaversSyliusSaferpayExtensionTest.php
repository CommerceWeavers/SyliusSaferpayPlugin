<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\DependencyInjection;

use CommerceWeavers\SyliusSaferpayPlugin\DependencyInjection\CommerceWeaversSyliusSaferpayExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

final class CommerceWeaversSyliusSaferpayExtensionTest extends AbstractExtensionTestCase
{
    /** @test */
    public function it_loads_parameters_properly(): void
    {
        $this->configureContainer();
        $this->load();

        $this->assertContainerBuilderHasParameter('commerce_weavers.saferpay.api_base_url', 'https://www.saferpay.com/api/');
        $this->assertContainerBuilderHasParameter('commerce_weavers.saferpay.test_api_base_url', 'https://test.saferpay.com/api/');
    }

    protected function getContainerExtensions(): array
    {
        return [new CommerceWeaversSyliusSaferpayExtension()];
    }

    private function configureContainer(): void
    {
        $this->container->setParameter('kernel.environment', 'test');
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.bundles_metadata', [
            'CommerceWeaversSyliusSaferpayPlugin' => [
                'path' => 'random_path',
            ],
        ]);
    }
}
