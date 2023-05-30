<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Status;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Payment\Model\PaymentInterface;

final class StatusCheckerSpec extends ObjectBehavior
{
    function it_returns_true_when_a_payment_status_is_new(PaymentInterface $payment): void
    {
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_NEW]);

        $this->isNew($payment)->shouldReturn(true);
    }

    function it_returns_false_when_a_payment_status_is_other_than_new(PaymentInterface $payment): void
    {
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_AUTHORIZED]);

        $this->isNew($payment)->shouldReturn(false);
    }

    function it_returns_true_when_a_payment_status_is_authorized(PaymentInterface $payment): void
    {
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_AUTHORIZED]);

        $this->isAuthorized($payment)->shouldReturn(true);
    }

    function it_returns_false_when_a_payment_status_is_other_than_authorized(PaymentInterface $payment): void
    {
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_CAPTURED]);

        $this->isAuthorized($payment)->shouldReturn(false);
    }

    function it_returns_true_when_a_payment_status_is_captured(PaymentInterface $payment): void
    {
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_CAPTURED]);

        $this->isCaptured($payment)->shouldReturn(true);
    }

    function it_returns_false_when_a_payment_status_is_other_than_captured(PaymentInterface $payment): void
    {
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_AUTHORIZED]);

        $this->isCaptured($payment)->shouldReturn(false);
    }

    function it_returns_true_when_a_payment_status_is_cancelled(PaymentInterface $payment): void
    {
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_CANCELLED]);

        $this->isCancelled($payment)->shouldReturn(true);
    }

    function it_returns_false_when_a_payment_status_is_other_than_cancelled(PaymentInterface $payment): void
    {
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_AUTHORIZED]);

        $this->isCancelled($payment)->shouldReturn(false);
    }

    function it_returns_true_when_a_payment_is_completed(PaymentInterface $payment): void
    {
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_CAPTURED]);
        $payment->getState()->willReturn(PaymentInterface::STATE_COMPLETED);

        $this->isCompleted($payment)->shouldReturn(true);
    }

    function it_returns_false_when_a_payment_is_not_completed_due_to_an_invalid_state(PaymentInterface $payment): void
    {
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_CAPTURED]);
        $payment->getState()->willReturn(PaymentInterface::STATE_AUTHORIZED);

        $this->isCompleted($payment)->shouldReturn(false);
    }

    function it_returns_false_when_a_payment_is_not_completed_due_to_an_invalid_status(PaymentInterface $payment): void
    {
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_AUTHORIZED]);
        $payment->getState()->willReturn(PaymentInterface::STATE_COMPLETED);

        $this->isCompleted($payment)->shouldReturn(false);
    }

    function it_returns_true_when_a_payment_status_is_refunded(PaymentInterface $payment): void
    {
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_REFUNDED]);

        $this->isRefunded($payment)->shouldReturn(true);
    }

    function it_returns_false_when_a_payment_status_is_other_than_refunded(PaymentInterface $payment): void
    {
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_AUTHORIZED]);

        $this->isRefunded($payment)->shouldReturn(false);
    }
}
