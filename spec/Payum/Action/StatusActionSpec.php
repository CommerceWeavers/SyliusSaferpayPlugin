<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StateMarkerInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Authorize;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class StatusActionSpec extends ObjectBehavior
{
    function let(StateMarkerInterface $stateMarker): void
    {
        $this->beConstructedWith($stateMarker);
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
        StateMarkerInterface $stateMarker,
    ): void {
        $request->getFirstModel()->willReturn($payment);

        $stateMarker->canBeMarkedAsNew($request)->willReturn(true);
        $stateMarker->markAsNew($request)->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }

    function it_marks_authorized_on_request_when_payment_status_is_authorized(
        GetStatus $request,
        SyliusPaymentInterface $payment,
        StateMarkerInterface $stateMarker,
    ): void {
        $request->getFirstModel()->willReturn($payment);

        $stateMarker->canBeMarkedAsNew($request)->willReturn(false);
        $stateMarker->markAsNew($request)->shouldNotBeCalled();
        $stateMarker->canBeMarkedAsAuthorized($request)->willReturn(true);
        $stateMarker->markAsAuthorized($request)->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }

    function it_marks_captured_on_request_when_payment_status_is_captured(
        GetStatus $request,
        SyliusPaymentInterface $payment,
        StateMarkerInterface $stateMarker,
    ): void {
        $request->getFirstModel()->willReturn($payment);

        $stateMarker->canBeMarkedAsNew($request)->willReturn(false);
        $stateMarker->markAsNew($request)->shouldNotBeCalled();
        $stateMarker->canBeMarkedAsAuthorized($request)->willReturn(false);
        $stateMarker->markAsAuthorized($request)->shouldNotBeCalled();
        $stateMarker->canBeMarkedAsCaptured($request)->willReturn(true);
        $stateMarker->markAsCaptured($request)->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }

    function it_marks_failed_on_request_when_payment_status_cannot_be_matched(
        GetStatus $request,
        SyliusPaymentInterface $payment,
        StateMarkerInterface $stateMarker,
    ): void {
        $request->getFirstModel()->willReturn($payment);

        $stateMarker->canBeMarkedAsNew($request)->willReturn(false);
        $stateMarker->markAsNew($request)->shouldNotBeCalled();
        $stateMarker->canBeMarkedAsAuthorized($request)->willReturn(false);
        $stateMarker->markAsAuthorized($request)->shouldNotBeCalled();
        $stateMarker->canBeMarkedAsCaptured($request)->willReturn(false);
        $stateMarker->markAsCaptured($request)->shouldNotBeCalled();
        $stateMarker->markAsFailed($request)->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }
}
