<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\RefundInterface;

interface RefundFactoryInterface
{
    public function createNewWithModel(object $model): RefundInterface;
}
