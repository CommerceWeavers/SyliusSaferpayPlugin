<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Status;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusCheckerInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;

final class StateMarkerSpec extends ObjectBehavior
{
    function let(StatusCheckerInterface $statusChecker): void
    {
        $this->beConstructedWith($statusChecker);
    }

    function it_returns_true_when_a_passed_status_request_can_be_marked_as_new(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isNew($model)->willReturn(true);

        $this->canBeMarkedAsNew($status)->shouldReturn(true);
    }

    function it_returns_false_when_a_passed_status_request_can_be_marked_as_new(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isNew($model)->willReturn(false);

        $this->canBeMarkedAsNew($status)->shouldReturn(false);
    }

    function it_returns_true_when_a_passed_status_request_can_be_marked_as_authorized(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isAuthorized($model)->willReturn(true);

        $this->canBeMarkedAsAuthorized($status)->shouldReturn(true);
    }

    function it_returns_false_when_a_passed_status_request_can_be_marked_as_authorized(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isAuthorized($model)->willReturn(false);

        $this->canBeMarkedAsAuthorized($status)->shouldReturn(false);
    }

    function it_returns_true_when_a_passed_status_request_can_be_marked_as_captured(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isCaptured($model)->willReturn(true);

        $this->canBeMarkedAsCaptured($status)->shouldReturn(true);
    }

    function it_returns_false_when_a_passed_status_request_can_be_marked_as_captured(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isCaptured($model)->willReturn(false);

        $this->canBeMarkedAsCaptured($status)->shouldReturn(false);
    }

    function it_returns_true_when_a_passed_status_request_can_be_marked_as_cancelled(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isCancelled($model)->willReturn(true);

        $this->canBeMarkedAsCancelled($status)->shouldReturn(true);
    }

    function it_returns_false_when_a_passed_status_request_can_be_marked_as_cancelled(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isCancelled($model)->willReturn(false);

        $this->canBeMarkedAsCancelled($status)->shouldReturn(false);
    }

    function it_marks_a_passed_status_request_as_new(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isNew($model)->willReturn(true);

        $status->markNew()->shouldBeCalled();

        $this->markAsNew($status);
    }

    function it_throws_an_exception_when_trying_to_mark_as_new_a_not_qualifying_status_request(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isNew($model)->willReturn(false);

        $status->markNew()->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('markAsNew', [$status]);
    }

    function it_marks_a_passed_status_request_as_authorized(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isAuthorized($model)->willReturn(true);

        $status->markAuthorized()->shouldBeCalled();

        $this->markAsAuthorized($status);
    }

    function it_throws_an_exception_when_trying_to_mark_as_authorized_a_not_qualifying_status_request(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isAuthorized($model)->willReturn(false);

        $status->markAuthorized()->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('markAsAuthorized', [$status]);
    }

    function it_marks_a_passed_status_request_as_captured(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isCaptured($model)->willReturn(true);

        $status->markCaptured()->shouldBeCalled();

        $this->markAsCaptured($status);
    }

    function it_throws_an_exception_when_trying_to_mark_as_captured_a_not_qualifying_status_request(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isCaptured($model)->willReturn(false);

        $status->markCaptured()->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('markAsCaptured', [$status]);
    }

    function it_marks_a_passed_status_request_as_cancelled(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isCancelled($model)->willReturn(true);

        $status->markCanceled()->shouldBeCalled();

        $this->markAsCancelled($status);
    }

    function it_throws_an_exception_when_trying_to_mark_as_cancelled_a_not_qualifying_status_request(
        StatusCheckerInterface $statusChecker,
        GetStatus $status,
        PaymentInterface $model,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isCancelled($model)->willReturn(false);

        $status->markCanceled()->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('markAsCancelled', [$status]);
    }

    function it_marks_a_passed_status_request_as_failed(
        GetStatus $status,
    ): void {
        $status->markFailed()->shouldBeCalled();

        $this->markAsFailed($status);
    }
}
