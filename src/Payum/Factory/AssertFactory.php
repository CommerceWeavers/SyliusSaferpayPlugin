<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Assert;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\AssertInterface;

final class AssertFactory implements AssertFactoryInterface
{
    public function createNewWithModel(object $model): AssertInterface
    {
        return new Assert($model);
    }
}
