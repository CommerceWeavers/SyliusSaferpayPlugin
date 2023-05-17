<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Refund;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\RefundInterface;

final class RefundFactory implements RefundFactoryInterface
{
    public function createNewWithModel(object $model): RefundInterface
    {
        return new Refund($model);
    }
}
