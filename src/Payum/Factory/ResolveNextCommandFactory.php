<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\ResolveNextCommand;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\ResolveNextCommandInterface;

final class ResolveNextCommandFactory implements ResolveNextCommandFactoryInterface
{
    public function createNewWithModel(object $model): ResolveNextCommandInterface
    {
        return new ResolveNextCommand($model);
    }
}
