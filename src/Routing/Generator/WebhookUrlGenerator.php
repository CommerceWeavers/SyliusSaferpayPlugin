<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Routing\Generator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final class WebhookUrlGenerator implements WebhookRouteGeneratorInterface
{
    private const COMMERCE_WEAVERS_SYLIUS_SAFERPAY_WEBHOOK_ROUTE = 'commerce_weavers_sylius_saferpay_webhook';

    public function __construct(private RouterInterface $router)
    {
    }

    public function generate(string $payumToken): string
    {
        return $this->router->generate(
            self::COMMERCE_WEAVERS_SYLIUS_SAFERPAY_WEBHOOK_ROUTE,
            ['payum_token' => $payumToken],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
