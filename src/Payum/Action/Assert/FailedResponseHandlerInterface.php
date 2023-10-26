<?php

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Assert;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use Sylius\Component\Core\Model\PaymentInterface;

interface FailedResponseHandlerInterface
{
    public function handle(PaymentInterface $payment, ErrorResponse $response): void;
}
