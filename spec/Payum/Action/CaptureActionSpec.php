<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusCheckerInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class CaptureActionSpec extends ObjectBehavior
{
    function let(SaferpayClientInterface $saferpayClient, StatusCheckerInterface $statusChecker): void
    {
        $this->beConstructedWith($saferpayClient, $statusChecker);
    }

    function it_supports_capture_request_and_payment_model(SyliusPaymentInterface $payment): void
    {
        $request = new Capture($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(true);
    }

    function it_does_not_support_other_requests_than_capture(SyliusPaymentInterface $payment): void
    {
        $request = new Authorize($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(false);
    }

    function it_does_not_support_capture_request_with_wrong_model(PayumPaymentInterface $payment): void
    {
        $request = new Capture($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(false);
    }

    function it_throws_an_exception_when_request_not_supported_on_execute(): void
    {
        $this
            ->shouldThrow(RequestNotSupportedException::class)
            ->during('execute', [new Authorize(new \stdClass())])
        ;
    }

    function it_does_nothing_if_payment_has_captured_status(
        SaferpayClientInterface $saferpayClient,
        StatusCheckerInterface $statusChecker,
        SyliusPaymentInterface $payment,
    ): void {
        $statusChecker->isCaptured($payment)->willReturn(true);

        $saferpayClient->capture($payment)->shouldNotBeCalled();
        $payment->setDetails(Argument::any())->shouldNotBeCalled();

        $this->execute(new Capture($payment->getWrappedObject()));
    }

    function it_captures_the_payment(
        SaferpayClientInterface $saferpayClient,
        StatusCheckerInterface $statusChecker,
        SyliusPaymentInterface $payment,
        CaptureResponse $captureResponse,
    ): void {
        $statusChecker->isCaptured($payment)->willReturn(false);

        $saferpayClient->capture($payment)->willReturn($captureResponse);
        $captureResponse->getStatus()->willReturn(StatusAction::STATUS_CAPTURED);
        $captureResponse->getCaptureId()->willReturn('0d7OYrAInYCWSASdzSh3bbr4jrSb_c');
        $captureResponse->getStatusCode()->willReturn(200);

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn([]);
        $payment
            ->setDetails(['status' => StatusAction::STATUS_CAPTURED, 'capture_id' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c'])
            ->shouldBeCalled()
        ;

        $this->execute(new Capture($payment->getWrappedObject()));
    }

    function it_marks_the_payment_as_failed_if_there_is_different_status_code_than_ok(
        SaferpayClientInterface $saferpayClient,
        SyliusPaymentInterface $payment,
        StatusCheckerInterface $statusChecker,
        ErrorResponse $errorResponse,
    ): void {
        $statusChecker->isCaptured($payment)->willReturn(false);

        $saferpayClient->capture($payment)->willReturn($errorResponse);
        $errorResponse->getTransactionId()->willReturn('TRANSACTION_ID');
        $errorResponse->getStatusCode()->willReturn(402);

        $payment->getDetails()->willReturn([]);
        $payment
            ->setDetails(['status' => StatusAction::STATUS_FAILED, 'transaction_id' => 'TRANSACTION_ID'])
            ->shouldBeCalled()
        ;

        $this->execute(new Capture($payment->getWrappedObject()));
    }
}
