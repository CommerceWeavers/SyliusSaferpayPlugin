<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Status;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use Sylius\Component\Payment\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class StatusChecker implements StatusCheckerInterface
{
    public function isNew(PaymentInterface $payment): bool
    {
        return StatusAction::STATUS_NEW === $this->getPaymentStatus($payment);
    }

    public function isAuthorized(PaymentInterface $payment): bool
    {
        return StatusAction::STATUS_AUTHORIZED === $this->getPaymentStatus($payment);
    }

    public function isCaptured(PaymentInterface $payment): bool
    {
        return StatusAction::STATUS_CAPTURED === $this->getPaymentStatus($payment);
    }

    public function isCancelled(PaymentInterface $payment): bool
    {
        return StatusAction::STATUS_CANCELLED === $this->getPaymentStatus($payment);
    }

    public function isCompleted(PaymentInterface $payment): bool
    {
        return $this->isCaptured($payment) && PaymentInterface::STATE_COMPLETED === $this->getPaymentState($payment);
    }

    public function isRefunded(PaymentInterface $payment): bool
    {
        return StatusAction::STATUS_REFUNDED === $this->getPaymentStatus($payment);
    }

    private function getPaymentState(PaymentInterface $payment): string
    {
        $state = $payment->getState();

        Assert::notNull($state);

        return $state;
    }

    private function getPaymentStatus(PaymentInterface $payment): string
    {
        $paymentDetails = $payment->getDetails();

        Assert::keyExists($paymentDetails, 'status');
        Assert::string($paymentDetails['status']);

        return $paymentDetails['status'];
    }
}
