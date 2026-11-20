<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\Status;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusChecker;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Payment\Model\PaymentInterface;

final class StatusCheckerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<PaymentInterface> */
    private ObjectProphecy $payment;

    private StatusChecker $statusChecker;

    protected function setUp(): void
    {
        $this->payment = $this->prophesize(PaymentInterface::class);
        $this->statusChecker = new StatusChecker();
    }

    public function test_it_returns_true_when_a_payment_status_is_new(): void
    {
        $this->payment->getDetails()->willReturn(['status' => StatusAction::STATUS_NEW]);

        self::assertTrue($this->statusChecker->isNew($this->payment->reveal()));
    }

    public function test_it_returns_false_when_a_payment_status_is_other_than_new(): void
    {
        $this->payment->getDetails()->willReturn(['status' => StatusAction::STATUS_AUTHORIZED]);

        self::assertFalse($this->statusChecker->isNew($this->payment->reveal()));
    }

    public function test_it_returns_true_when_a_payment_status_is_authorized(): void
    {
        $this->payment->getDetails()->willReturn(['status' => StatusAction::STATUS_AUTHORIZED]);

        self::assertTrue($this->statusChecker->isAuthorized($this->payment->reveal()));
    }

    public function test_it_returns_false_when_a_payment_status_is_other_than_authorized(): void
    {
        $this->payment->getDetails()->willReturn(['status' => StatusAction::STATUS_CAPTURED]);

        self::assertFalse($this->statusChecker->isAuthorized($this->payment->reveal()));
    }

    public function test_it_returns_true_when_a_payment_status_is_captured(): void
    {
        $this->payment->getDetails()->willReturn(['status' => StatusAction::STATUS_CAPTURED]);

        self::assertTrue($this->statusChecker->isCaptured($this->payment->reveal()));
    }

    public function test_it_returns_false_when_a_payment_status_is_other_than_captured(): void
    {
        $this->payment->getDetails()->willReturn(['status' => StatusAction::STATUS_AUTHORIZED]);

        self::assertFalse($this->statusChecker->isCaptured($this->payment->reveal()));
    }

    public function test_it_returns_true_when_a_payment_status_is_cancelled(): void
    {
        $this->payment->getDetails()->willReturn(['status' => StatusAction::STATUS_CANCELLED]);

        self::assertTrue($this->statusChecker->isCancelled($this->payment->reveal()));
    }

    public function test_it_returns_false_when_a_payment_status_is_other_than_cancelled(): void
    {
        $this->payment->getDetails()->willReturn(['status' => StatusAction::STATUS_AUTHORIZED]);

        self::assertFalse($this->statusChecker->isCancelled($this->payment->reveal()));
    }

    public function test_it_returns_true_when_a_payment_is_completed(): void
    {
        $this->payment->getDetails()->willReturn(['status' => StatusAction::STATUS_CAPTURED]);
        $this->payment->getState()->willReturn(PaymentInterface::STATE_COMPLETED);

        self::assertTrue($this->statusChecker->isCompleted($this->payment->reveal()));
    }

    public function test_it_returns_false_when_a_payment_is_not_completed_due_to_an_invalid_state(): void
    {
        $this->payment->getDetails()->willReturn(['status' => StatusAction::STATUS_CAPTURED]);
        $this->payment->getState()->willReturn(PaymentInterface::STATE_AUTHORIZED);

        self::assertFalse($this->statusChecker->isCompleted($this->payment->reveal()));
    }

    public function test_it_returns_false_when_a_payment_is_not_completed_due_to_an_invalid_status(): void
    {
        $this->payment->getDetails()->willReturn(['status' => StatusAction::STATUS_AUTHORIZED]);
        $this->payment->getState()->willReturn(PaymentInterface::STATE_COMPLETED);

        self::assertFalse($this->statusChecker->isCompleted($this->payment->reveal()));
    }

    public function test_it_returns_true_when_a_payment_status_is_refunded(): void
    {
        $this->payment->getDetails()->willReturn(['status' => StatusAction::STATUS_REFUNDED]);

        self::assertTrue($this->statusChecker->isRefunded($this->payment->reveal()));
    }

    public function test_it_returns_false_when_a_payment_status_is_other_than_refunded(): void
    {
        $this->payment->getDetails()->willReturn(['status' => StatusAction::STATUS_AUTHORIZED]);

        self::assertFalse($this->statusChecker->isRefunded($this->payment->reveal()));
    }
}
