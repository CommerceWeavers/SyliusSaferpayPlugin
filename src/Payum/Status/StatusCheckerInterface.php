<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Status;

use Sylius\Component\Payment\Model\PaymentInterface;

interface StatusCheckerInterface
{
    public function isNew(PaymentInterface $payment): bool;

    public function isAuthorized(PaymentInterface $payment): bool;

    public function isCaptured(PaymentInterface $payment): bool;

    public function isCancelled(PaymentInterface $payment): bool;

    public function isCompleted(PaymentInterface $payment): bool;

    public function isRefunded(PaymentInterface $payment): bool;

    public function isRefundFailed(PaymentInterface $payment): bool;
}
