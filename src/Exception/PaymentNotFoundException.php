<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Exception;

final class PaymentNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Payment cannot be found');
    }
}
