<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusMarkerInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Authorize;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class StatusActionSpec extends ObjectBehavior
{
    function let(StatusMarkerInterface $statusMarker): void
    {
        $this->beConstructedWith($statusMarker);
    }

    function it_supports_get_status_request_and_payment_model(SyliusPaymentInterface $payment): void
    {
        $request = new GetStatus($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(true);
    }

    function it_does_not_support_other_requests_than_get_status(SyliusPaymentInterface $payment): void
    {
        $request = new Authorize($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(false);
    }

    function it_does_not_support_get_status_request_with_wrong_model(PayumPaymentInterface $payumPayment): void
    {
        $request = new GetStatus($payumPayment->getWrappedObject());

        $this->supports($request)->shouldReturn(false);
    }

    function it_throws_an_exception_when_request_not_supported_on_execute(): void
    {
        $this
            ->shouldThrow(RequestNotSupportedException::class)
            ->during('execute', [new Authorize(new \stdClass())])
        ;
    }

    function it_marks_new_on_request_when_payment_status_is_new(
        GetStatus $request,
        SyliusPaymentInterface $payment,
        StatusMarkerInterface $statusMarker,
    ): void {
        $request->getFirstModel()->willReturn($payment);

        $statusMarker->canBeMarkedAsNew($request)->willReturn(true);
        $statusMarker->markAsNew($request)->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }

    function it_marks_authorized_on_request_when_payment_status_is_authorized(
        GetStatus $request,
        SyliusPaymentInterface $payment,
        StatusMarkerInterface $statusMarker,
    ): void {
        $request->getFirstModel()->willReturn($payment);

        $statusMarker->canBeMarkedAsNew($request)->willReturn(false);
        $statusMarker->markAsNew($request)->shouldNotBeCalled();
        $statusMarker->canBeMarkedAsAuthorized($request)->willReturn(true);
        $statusMarker->markAsAuthorized($request)->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }

    function it_marks_captured_on_request_when_payment_status_is_captured(
        GetStatus $request,
        SyliusPaymentInterface $payment,
        StatusMarkerInterface $statusMarker,
    ): void {
        $request->getFirstModel()->willReturn($payment);

        $statusMarker->canBeMarkedAsNew($request)->willReturn(false);
        $statusMarker->markAsNew($request)->shouldNotBeCalled();
        $statusMarker->canBeMarkedAsAuthorized($request)->willReturn(false);
        $statusMarker->markAsAuthorized($request)->shouldNotBeCalled();
        $statusMarker->canBeMarkedAsCaptured($request)->willReturn(true);
        $statusMarker->markAsCaptured($request)->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }

    function it_marks_cancelled_on_request_when_payment_status_is_captured(
        GetStatus $request,
        SyliusPaymentInterface $payment,
        StatusMarkerInterface $statusMarker,
    ): void {
        $request->getFirstModel()->willReturn($payment);

        $statusMarker->canBeMarkedAsNew($request)->willReturn(false);
        $statusMarker->markAsNew($request)->shouldNotBeCalled();
        $statusMarker->canBeMarkedAsAuthorized($request)->willReturn(false);
        $statusMarker->markAsAuthorized($request)->shouldNotBeCalled();
        $statusMarker->canBeMarkedAsCaptured($request)->willReturn(false);
        $statusMarker->markAsCaptured($request)->shouldNotBeCalled();
        $statusMarker->canBeMarkedAsCancelled($request)->willReturn(true);
        $statusMarker->markAsCancelled($request)->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }

    function it_marks_refunded_on_request_when_payment_status_is_refunded(
        GetStatus $request,
        SyliusPaymentInterface $payment,
        StatusMarkerInterface $statusMarker,
    ): void {
        $request->getFirstModel()->willReturn($payment);

        $statusMarker->canBeMarkedAsNew($request)->willReturn(false);
        $statusMarker->markAsNew($request)->shouldNotBeCalled();
        $statusMarker->canBeMarkedAsAuthorized($request)->willReturn(false);
        $statusMarker->markAsAuthorized($request)->shouldNotBeCalled();
        $statusMarker->canBeMarkedAsCaptured($request)->willReturn(false);
        $statusMarker->markAsCaptured($request)->shouldNotBeCalled();
        $statusMarker->canBeMarkedAsCancelled($request)->willReturn(false);
        $statusMarker->markAsCancelled($request)->shouldNotBeCalled();
        $statusMarker->canBeMarkedAsRefunded($request)->willReturn(true);
        $statusMarker->markAsRefunded($request)->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }

    function it_marks_failed_on_request_when_payment_status_cannot_be_matched(
        GetStatus $request,
        SyliusPaymentInterface $payment,
        StatusMarkerInterface $statusMarker,
    ): void {
        $request->getFirstModel()->willReturn($payment);

        $statusMarker->canBeMarkedAsNew($request)->willReturn(false);
        $statusMarker->markAsNew($request)->shouldNotBeCalled();
        $statusMarker->canBeMarkedAsAuthorized($request)->willReturn(false);
        $statusMarker->markAsAuthorized($request)->shouldNotBeCalled();
        $statusMarker->canBeMarkedAsCaptured($request)->willReturn(false);
        $statusMarker->markAsCaptured($request)->shouldNotBeCalled();
        $statusMarker->canBeMarkedAsCancelled($request)->willReturn(false);
        $statusMarker->markAsCancelled($request)->shouldNotBeCalled();
        $statusMarker->canBeMarkedAsRefunded($request)->willReturn(false);
        $statusMarker->markAsRefunded($request)->shouldNotBeCalled();
        $statusMarker->markAsFailed($request)->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }
}
