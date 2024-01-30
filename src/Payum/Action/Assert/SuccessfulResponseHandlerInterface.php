<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Assert;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use Sylius\Component\Core\Model\PaymentInterface;

interface SuccessfulResponseHandlerInterface
{
    public function handle(PaymentInterface $payment, AssertResponse $response): void;
}
