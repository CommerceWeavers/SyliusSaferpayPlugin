<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

final class ErrorName
{
    public const TRANSACTION_ABORTED = 'TRANSACTION_ABORTED';

    public const TRANSACTION_DECLINED = 'TRANSACTION_DECLINED';

    private function __construct()
    {
    }
}
