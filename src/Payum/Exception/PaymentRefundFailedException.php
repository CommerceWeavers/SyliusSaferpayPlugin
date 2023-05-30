<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception;

final class PaymentRefundFailedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Payment refund has failed');
    }
}
