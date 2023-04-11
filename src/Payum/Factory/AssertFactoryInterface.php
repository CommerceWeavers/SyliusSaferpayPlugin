<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\AssertInterface;

interface AssertFactoryInterface
{
    public function createNewWithModel(object $model): AssertInterface;
}
