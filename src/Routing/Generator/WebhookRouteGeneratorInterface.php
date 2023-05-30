<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Routing\Generator;

interface WebhookRouteGeneratorInterface
{
    public function generate(string $payumToken): string;
}
