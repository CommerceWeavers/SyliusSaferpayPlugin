<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Provider;

interface UuidProviderInterface
{
    public function provide(): string;
}
