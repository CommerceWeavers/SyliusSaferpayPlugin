<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Status;

use Sylius\Component\Core\Model\PaymentInterface;

interface StatusCheckerInterface
{
    public function isNew(PaymentInterface $payment): bool;

    public function isAuthorized(PaymentInterface $payment): bool;

    public function isCaptured(PaymentInterface $payment): bool;

    public function isCompleted(PaymentInterface $payment): bool;
}
