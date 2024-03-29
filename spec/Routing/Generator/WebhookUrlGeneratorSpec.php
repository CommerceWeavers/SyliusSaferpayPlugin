<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Routing\Generator;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Routing\RouterInterface;

final class WebhookUrlGeneratorSpec extends ObjectBehavior
{
    function let(RouterInterface $router): void
    {
        $this->beConstructedWith($router);
    }

    function it_generates_webhook_url(RouterInterface $router): void
    {
        $router->generate(
            'commerce_weavers_sylius_saferpay_webhook',
            ['payum_token' => 'abc123', 'order_token' => 'TOKEN'],
            0,
        )->shouldBeCalled()->willReturn('/saferpay/webhook/abc123/TOKEN');

        $this->generate('abc123', 'TOKEN')->shouldReturn('/saferpay/webhook/abc123/TOKEN');
    }
}
