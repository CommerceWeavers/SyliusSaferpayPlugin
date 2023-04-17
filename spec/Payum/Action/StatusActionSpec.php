<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\GetStatusInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRoute;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class StatusActionSpec extends ObjectBehavior
{
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
    ): void {
        $request->getFirstModel()->willReturn($payment);
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_NEW]);

        $request->markNew()->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }

    function it_marks_authorized_on_request_when_payment_status_is_authorized(
        GetStatus $request,
        SyliusPaymentInterface $payment,
    ): void {
        $request->getFirstModel()->willReturn($payment);
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_AUTHORIZED]);

        $request->markAuthorized()->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }

    function it_marks_captured_on_request_when_payment_status_is_captured(
        GetStatus $request,
        SyliusPaymentInterface $payment,
    ): void {
        $request->getFirstModel()->willReturn($payment);
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_CAPTURED]);

        $request->markCaptured()->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }

    function it_marks_failed_on_request_when_payment_status_cannot_be_matched(
        GetStatus $request,
        SyliusPaymentInterface $payment,
    ): void {
        $request->getFirstModel()->willReturn($payment);
        $payment->getDetails()->willReturn(['status' => 'wrong_status']);

        $request->markFailed()->shouldBeCalled();

        $this->execute($request->getWrappedObject());
    }
}
