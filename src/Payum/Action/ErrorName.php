<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

final class ErrorName
{
    public const TRANSACTION_ABORTED = 'TRANSACTION_ABORTED';

    public const TRANSACTION_DECLINED = 'TRANSACTION_DECLINED';

    public const TRANSACTION_ALREADY_CAPTURED = 'TRANSACTION_ALREADY_CAPTURED';

    private function __construct()
    {
    }
}
