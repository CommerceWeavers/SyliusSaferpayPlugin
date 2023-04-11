<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\DependencyInjection;

use CommerceWeavers\SyliusSaferpayPlugin\DependencyInjection\CommerceWeaversSyliusSaferpayExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

final class CommerceWeaversSyliusSaferpayExtensionTest extends AbstractExtensionTestCase
{
    /** @test */
    public function it_loads_api_base_url_parameter_properly_for_enabled_sandbox(): void
    {
        $this->configureContainer();
        $this->load();

        $this->assertContainerBuilderHasParameter('commerce_weavers.saferpay.api_base_url', 'https://test.saferpay.com/api/');
    }

    /** @test */
    public function it_loads_api_base_url_parameter_properly_for_disabled_sandbox(): void
    {
        $this->configureContainer();
        $this->load(['sandbox' => false]);

        $this->assertContainerBuilderHasParameter('commerce_weavers.saferpay.api_base_url', 'https://saferpay.com/api/');
    }

    protected function getContainerExtensions(): array
    {
        return [new CommerceWeaversSyliusSaferpayExtension()];
    }

    private function configureContainer(): void
    {
        $this->container->setParameter('kernel.environment', 'test');
        $this->container->setParameter('kernel.debug', true);
    }
}
