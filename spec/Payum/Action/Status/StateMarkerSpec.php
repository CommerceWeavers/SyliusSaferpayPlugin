<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Status;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Status\StatusCheckerInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;

final class StateMarkerSpec extends ObjectBehavior
{
    function let(StatusCheckerInterface $statusChecker): void
    {
        $this->beConstructedWith($statusChecker);
    }

    function it_returns_true_status_request_can_be_marked_as_new(
        GetStatus $status,
        PaymentInterface $model,
        StatusCheckerInterface $statusChecker,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isNew($model)->willReturn(true);

        $this->canBeMarkedAsNew($status)->shouldReturn(true);
    }

    function it_returns_false_status_request_can_be_marked_as_new(
        GetStatus $status,
        PaymentInterface $model,
        StatusCheckerInterface $statusChecker,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isNew($model)->willReturn(false);

        $this->canBeMarkedAsNew($status)->shouldReturn(false);
    }

    function it_returns_true_status_request_can_be_marked_as_authorized(
        GetStatus $status,
        PaymentInterface $model,
        StatusCheckerInterface $statusChecker,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isAuthorized($model)->willReturn(true);

        $this->canBeMarkedAsAuthorized($status)->shouldReturn(true);
    }

    function it_returns_false_status_request_can_be_marked_as_authorized(
        GetStatus $status,
        PaymentInterface $model,
        StatusCheckerInterface $statusChecker,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isAuthorized($model)->willReturn(false);

        $this->canBeMarkedAsAuthorized($status)->shouldReturn(false);
    }

    function it_returns_true_status_request_can_be_marked_as_captured(
        GetStatus $status,
        PaymentInterface $model,
        StatusCheckerInterface $statusChecker,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isCaptured($model)->willReturn(true);

        $this->canBeMarkedAsCaptured($status)->shouldReturn(true);
    }

    function it_returns_false_status_request_can_be_marked_as_captured(
        GetStatus $status,
        PaymentInterface $model,
        StatusCheckerInterface $statusChecker,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isCaptured($model)->willReturn(false);

        $this->canBeMarkedAsCaptured($status)->shouldReturn(false);
    }

    function it_marks_status_request_as_new(
        GetStatus $status,
        PaymentInterface $model,
        StatusCheckerInterface $statusChecker,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isNew($model)->willReturn(true);

        $status->markNew()->shouldBeCalled();

        $this->markAsNew($status);
    }

    function it_throws_exception_when_trying_to_mark_as_new_not_qualifying_status_request(
        GetStatus $status,
        PaymentInterface $model,
        StatusCheckerInterface $statusChecker,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isNew($model)->willReturn(false);

        $status->markNew()->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('markAsNew', [$status]);
    }

    function it_marks_status_request_as_authorized(
        GetStatus $status,
        PaymentInterface $model,
        StatusCheckerInterface $statusChecker,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isAuthorized($model)->willReturn(true);

        $status->markAuthorized()->shouldBeCalled();

        $this->markAsAuthorized($status);
    }

    function it_throws_exception_when_trying_to_mark_as_authorized_not_qualifying_status_request(
        GetStatus $status,
        PaymentInterface $model,
        StatusCheckerInterface $statusChecker,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isAuthorized($model)->willReturn(false);

        $status->markAuthorized()->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('markAsAuthorized', [$status]);
    }

    function it_marks_status_request_as_captured(
        GetStatus $status,
        PaymentInterface $model,
        StatusCheckerInterface $statusChecker,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isCaptured($model)->willReturn(true);

        $status->markCaptured()->shouldBeCalled();

        $this->markAsCaptured($status);
    }

    function it_throws_exception_when_trying_to_mark_as_captured_not_qualifying_status_request(
        GetStatus $status,
        PaymentInterface $model,
        StatusCheckerInterface $statusChecker,
    ): void {
        $status->getFirstModel()->willReturn($model);
        $statusChecker->isCaptured($model)->willReturn(false);

        $status->markCaptured()->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('markAsCaptured', [$status]);
    }

    function it_marks_status_request_as_failed(
        GetStatus $status,
    ): void {
        $status->markFailed()->shouldBeCalled();

        $this->markAsFailed($status);
    }
}
