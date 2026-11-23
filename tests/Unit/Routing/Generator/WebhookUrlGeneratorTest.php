<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Routing\Generator;

use CommerceWeavers\SyliusSaferpayPlugin\Routing\Generator\WebhookUrlGenerator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Routing\RouterInterface;

final class WebhookUrlGeneratorTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_generates_webhook_url(): void
    {
        $router = $this->prophesize(RouterInterface::class);
        $router->generate(
            'commerce_weavers_sylius_saferpay_webhook',
            ['payum_token' => 'abc123', 'order_token' => 'TOKEN'],
            0,
        )->shouldBeCalled()->willReturn('/saferpay/webhook/abc123/TOKEN');

        $generator = new WebhookUrlGenerator($router->reveal());

        $this->assertSame('/saferpay/webhook/abc123/TOKEN', $generator->generate('abc123', 'TOKEN'));
    }
}
