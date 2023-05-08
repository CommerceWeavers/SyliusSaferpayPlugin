<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Request;

use Payum\Core\Request\Refund as PayumRefund;

final class Refund extends PayumRefund implements RefundInterface
{
}
