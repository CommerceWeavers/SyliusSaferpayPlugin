<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Event\Handler\Exception;

use Exception;

class PaymentNotFound extends Exception
{
    public function __construct(int $paymentId, int $code = 0, Exception $previous = null)
    {
        $message = sprintf('Payment with id %d not found', $paymentId);
        parent::__construct($message, $code, $previous);
    }
}
