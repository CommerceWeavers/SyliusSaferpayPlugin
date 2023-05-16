<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\ResolveNextCommandInterface;

interface ResolveNextCommandFactoryInterface
{
    public function createNewWithModel(object $model): ResolveNextCommandInterface;
}
