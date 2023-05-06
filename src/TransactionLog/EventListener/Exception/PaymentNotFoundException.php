<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\Exception;

use Exception;
use Throwable;

class PaymentNotFoundException extends Exception
{
    public function __construct (
        int $paymentId,
        int $code = 0,
        Throwable $previous = null
    ) {
        $message = sprintf('Payment with id %d not found', $paymentId);
        parent::__construct($message, $code, $previous);
    }
}
