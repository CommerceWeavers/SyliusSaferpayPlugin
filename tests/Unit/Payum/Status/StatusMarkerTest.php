<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\Status;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\StatusCannotBeAuthorizedException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\StatusCannotBeCancelledException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\StatusCannotBeCapturedException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\StatusCannotBeNewException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusCheckerInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusMarker;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;

final class StatusMarkerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<StatusCheckerInterface> */
    private ObjectProphecy $statusChecker;

    /** @var ObjectProphecy<GetStatus> */
    private ObjectProphecy $status;

    /** @var ObjectProphecy<PaymentInterface> */
    private ObjectProphecy $model;

    private StatusMarker $statusMarker;

    protected function setUp(): void
    {
        $this->statusChecker = $this->prophesize(StatusCheckerInterface::class);
        $this->status = $this->prophesize(GetStatus::class);
        $this->model = $this->prophesize(PaymentInterface::class);

        $this->statusMarker = new StatusMarker($this->statusChecker->reveal());
    }

    public function test_it_returns_true_when_a_passed_status_request_can_be_marked_as_new(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isNew($this->model->reveal())->willReturn(true);

        self::assertTrue($this->statusMarker->canBeMarkedAsNew($this->status->reveal()));
    }

    public function test_it_returns_false_when_a_passed_status_request_can_be_marked_as_new(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isNew($this->model->reveal())->willReturn(false);

        self::assertFalse($this->statusMarker->canBeMarkedAsNew($this->status->reveal()));
    }

    public function test_it_returns_true_when_a_passed_status_request_can_be_marked_as_authorized(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isAuthorized($this->model->reveal())->willReturn(true);

        self::assertTrue($this->statusMarker->canBeMarkedAsAuthorized($this->status->reveal()));
    }

    public function test_it_returns_false_when_a_passed_status_request_can_be_marked_as_authorized(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isAuthorized($this->model->reveal())->willReturn(false);

        self::assertFalse($this->statusMarker->canBeMarkedAsAuthorized($this->status->reveal()));
    }

    public function test_it_returns_true_when_a_passed_status_request_can_be_marked_as_captured(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isCaptured($this->model->reveal())->willReturn(true);

        self::assertTrue($this->statusMarker->canBeMarkedAsCaptured($this->status->reveal()));
    }

    public function test_it_returns_false_when_a_passed_status_request_can_be_marked_as_captured(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isCaptured($this->model->reveal())->willReturn(false);

        self::assertFalse($this->statusMarker->canBeMarkedAsCaptured($this->status->reveal()));
    }

    public function test_it_returns_true_when_a_passed_status_request_can_be_marked_as_cancelled(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isCancelled($this->model->reveal())->willReturn(true);

        self::assertTrue($this->statusMarker->canBeMarkedAsCancelled($this->status->reveal()));
    }

    public function test_it_returns_false_when_a_passed_status_request_can_be_marked_as_cancelled(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isCancelled($this->model->reveal())->willReturn(false);

        self::assertFalse($this->statusMarker->canBeMarkedAsCancelled($this->status->reveal()));
    }

    public function test_it_marks_a_passed_status_request_as_new(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isNew($this->model->reveal())->willReturn(true);

        $this->status->markNew()->shouldBeCalled();

        $this->statusMarker->markAsNew($this->status->reveal());
    }

    public function test_it_throws_an_exception_when_trying_to_mark_as_new_a_not_qualifying_status_request(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isNew($this->model->reveal())->willReturn(false);

        $this->status->markNew()->shouldNotBeCalled();

        $this->expectException(StatusCannotBeNewException::class);

        $this->statusMarker->markAsNew($this->status->reveal());
    }

    public function test_it_marks_a_passed_status_request_as_authorized(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isAuthorized($this->model->reveal())->willReturn(true);

        $this->status->markAuthorized()->shouldBeCalled();

        $this->statusMarker->markAsAuthorized($this->status->reveal());
    }

    public function test_it_throws_an_exception_when_trying_to_mark_as_authorized_a_not_qualifying_status_request(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isAuthorized($this->model->reveal())->willReturn(false);

        $this->status->markAuthorized()->shouldNotBeCalled();

        $this->expectException(StatusCannotBeAuthorizedException::class);

        $this->statusMarker->markAsAuthorized($this->status->reveal());
    }

    public function test_it_marks_a_passed_status_request_as_captured(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isCaptured($this->model->reveal())->willReturn(true);

        $this->status->markCaptured()->shouldBeCalled();

        $this->statusMarker->markAsCaptured($this->status->reveal());
    }

    public function test_it_throws_an_exception_when_trying_to_mark_as_captured_a_not_qualifying_status_request(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isCaptured($this->model->reveal())->willReturn(false);

        $this->status->markCaptured()->shouldNotBeCalled();

        $this->expectException(StatusCannotBeCapturedException::class);

        $this->statusMarker->markAsCaptured($this->status->reveal());
    }

    public function test_it_marks_a_passed_status_request_as_cancelled(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isCancelled($this->model->reveal())->willReturn(true);

        $this->status->markCanceled()->shouldBeCalled();

        $this->statusMarker->markAsCancelled($this->status->reveal());
    }

    public function test_it_throws_an_exception_when_trying_to_mark_as_cancelled_a_not_qualifying_status_request(): void
    {
        $this->status->getFirstModel()->willReturn($this->model->reveal());
        $this->statusChecker->isCancelled($this->model->reveal())->willReturn(false);

        $this->status->markCanceled()->shouldNotBeCalled();

        $this->expectException(StatusCannotBeCancelledException::class);

        $this->statusMarker->markAsCancelled($this->status->reveal());
    }

    public function test_it_marks_a_passed_status_request_as_failed(): void
    {
        $this->status->markFailed()->shouldBeCalled();

        $this->statusMarker->markAsFailed($this->status->reveal());
    }
}
