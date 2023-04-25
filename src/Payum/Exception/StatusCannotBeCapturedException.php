<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception;

final class StatusCannotBeCapturedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Status cannot be marked as captured');
    }
}
