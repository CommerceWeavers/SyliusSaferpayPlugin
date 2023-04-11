<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\DependencyInjection;

use CommerceWeavers\SyliusSaferpayPlugin\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /** @test */
    public function it_configures_sandbox_as_true_by_default(): void
    {
        $this->assertProcessedConfigurationEquals(
            [[]],
            ['sandbox' => true],
            'sandbox',
        );
    }

    /** @test */
    public function it_allows_for_setting_sandbox_as_false(): void
    {
        $this->assertProcessedConfigurationEquals(
            [['sandbox' => false]],
            ['sandbox' => false],
            'sandbox',
        );
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
