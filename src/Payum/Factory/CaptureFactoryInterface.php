<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory;

use Payum\Core\Request\Capture;

interface CaptureFactoryInterface
{
    public function createNewWithModel(object $model): Capture;
}
