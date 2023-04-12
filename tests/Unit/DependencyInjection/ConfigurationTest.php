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
    public function it_configures_api_base_url_with_default_value(): void
    {
        $this->assertProcessedConfigurationEquals(
            [[]],
            ['api_base_url' => 'https://saferpay.com/api/'],
            'api_base_url',
        );
    }

    /** @test */
    public function it_allows_for_setting_api_base_url(): void
    {
        $this->assertProcessedConfigurationEquals(
            [['api_base_url' => 'https://differenturl.com/api/']],
            ['api_base_url' => 'https://differenturl.com/api/'],
            'api_base_url',
        );
    }

    /** @test */
    public function it_configures_test_api_base_url_with_default_value(): void
    {
        $this->assertProcessedConfigurationEquals(
            [[]],
            ['test_api_base_url' => 'https://test.saferpay.com/api/'],
            'test_api_base_url',
        );
    }

    /** @test */
    public function it_allows_for_setting_test_api_base_url(): void
    {
        $this->assertProcessedConfigurationEquals(
            [['test_api_base_url' => 'https://differenturl.com/api/']],
            ['test_api_base_url' => 'https://differenturl.com/api/'],
            'test_api_base_url',
        );
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
