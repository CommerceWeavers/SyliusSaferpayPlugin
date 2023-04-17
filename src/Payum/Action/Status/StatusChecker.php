<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Status;

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

    public function isCompleted(PaymentInterface $payment): bool
    {
        return $this->isCaptured($payment) && PaymentInterface::STATE_COMPLETED === $this->getPaymentState($payment);
    }

    private function getPaymentState(PaymentInterface $payment): string
    {
        $state = $payment->getState();

        Assert::notNull($state);

        return $state;
    }

    private function getPaymentStatus(PaymentInterface $payment): string
    {
        /** @var array{status: string|null} $paymentDetails */
        $paymentDetails = $payment->getDetails();

        Assert::keyExists($paymentDetails, 'status');
        Assert::notNull($paymentDetails['status']);

        return $paymentDetails['status'];
    }
}
