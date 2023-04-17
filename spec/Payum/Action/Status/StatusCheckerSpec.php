<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Status;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Payment\Model\PaymentInterface;

final class StatusCheckerSpec extends ObjectBehavior
{
    function it_returns_true_when_payment_status_is_new(PaymentInterface $payment): void
    {
        $details = [
            'status' => StatusAction::STATUS_NEW,
        ];
        $payment->getDetails()->willReturn($details);

        $this->isNew($payment)->shouldReturn(true);
    }

    function it_returns_false_when_payment_status_is_other_than_new(PaymentInterface $payment): void
    {
        $details = [
            'status' => StatusAction::STATUS_AUTHORIZED,
        ];
        $payment->getDetails()->willReturn($details);

        $this->isNew($payment)->shouldReturn(false);
    }

    function it_returns_true_when_payment_status_is_authorized(PaymentInterface $payment): void
    {
        $details = [
            'status' => StatusAction::STATUS_AUTHORIZED,
        ];
        $payment->getDetails()->willReturn($details);

        $this->isAuthorized($payment)->shouldReturn(true);
    }

    function it_returns_false_when_payment_status_is_other_than_authorized(PaymentInterface $payment): void
    {
        $details = [
            'status' => StatusAction::STATUS_CAPTURED,
        ];
        $payment->getDetails()->willReturn($details);

        $this->isAuthorized($payment)->shouldReturn(false);
    }

    function it_returns_true_when_payment_status_is_captured(PaymentInterface $payment): void
    {
        $details = [
            'status' => StatusAction::STATUS_CAPTURED,
        ];
        $payment->getDetails()->willReturn($details);

        $this->isCaptured($payment)->shouldReturn(true);
    }

    function it_returns_false_when_payment_status_is_other_than_captured(PaymentInterface $payment): void
    {
        $details = [
            'status' => StatusAction::STATUS_AUTHORIZED,
        ];
        $payment->getDetails()->willReturn($details);

        $this->isCaptured($payment)->shouldReturn(false);
    }

    function it_returns_true_when_payment_is_completed(PaymentInterface $payment): void
    {
        $details = [
            'status' => StatusAction::STATUS_CAPTURED,
        ];
        $payment->getDetails()->willReturn($details);
        $payment->getState()->willReturn(PaymentInterface::STATE_COMPLETED);

        $this->isCompleted($payment)->shouldReturn(true);
    }

    function it_returns_false_when_payment_is_not_completed_due_to_invalid_state(PaymentInterface $payment): void
    {
        $details = [
            'status' => StatusAction::STATUS_CAPTURED,
        ];
        $payment->getDetails()->willReturn($details);
        $payment->getState()->willReturn(PaymentInterface::STATE_AUTHORIZED);

        $this->isCompleted($payment)->shouldReturn(false);
    }

    function it_returns_false_when_payment_is_not_completed_due_to_invalid_status(PaymentInterface $payment): void
    {
        $details = [
            'status' => StatusAction::STATUS_AUTHORIZED,
        ];
        $payment->getDetails()->willReturn($details);
        $payment->getState()->willReturn(PaymentInterface::STATE_COMPLETED);

        $this->isCompleted($payment)->shouldReturn(false);
    }
}
