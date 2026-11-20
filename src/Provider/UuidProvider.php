<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Provider;

use Symfony\Component\Uid\Uuid;

final class UuidProvider implements UuidProviderInterface
{
    public function provide(): string
    {
        return Uuid::v4()->toRfc4122();
    }
}
