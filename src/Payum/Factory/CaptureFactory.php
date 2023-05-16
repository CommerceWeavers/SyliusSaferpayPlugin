<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory;

use Payum\Core\Request\Capture;

final class CaptureFactory implements CaptureFactoryInterface
{
    public function createNewWithModel(object $model): Capture
    {
        return new Capture($model);
    }
}
