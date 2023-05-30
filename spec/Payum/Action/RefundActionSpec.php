<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Transaction;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\PaymentRefundFailedException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Refund;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\RefundInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

final class RefundActionSpec extends ObjectBehavior
{
    function let(SaferpayClientInterface $saferpayClient): void
    {
        $this->beConstructedWith($saferpayClient);
    }

    function it_supports_refund_request_and_payment_model(SyliusPaymentInterface $payment): void
    {
        $request = new Refund($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(true);
    }

    function it_does_not_support_other_requests_than_refund(SyliusPaymentInterface $payment): void
    {
        $request = new Capture($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(false);
    }

    function it_does_not_support_refund_request_with_wrong_model(PayumPaymentInterface $payment): void
    {
        $request = new Refund($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(false);
    }

    function it_throws_an_exception_when_request_not_supported_on_execute(): void
    {
        $this
            ->shouldThrow(RequestNotSupportedException::class)
            ->during('execute', [new Capture(new \stdClass())])
        ;
    }

    function it_throws_an_exception_when_request_has_no_token(
        SyliusPaymentInterface $payment,
        Authorize $request,
    ): void {
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('execute', [$request->getWrappedObject()])
        ;
    }

    function it_refunds_the_payment(
        SaferpayClientInterface $saferpayClient,
        SyliusPaymentInterface $payment,
        RefundInterface $request,
        TokenInterface $token,
        RefundResponse $refundResponse,
        Transaction $transaction,
        CaptureResponse $captureResponse,
    ): void {
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);

        $payment->getDetails()->willReturn([]);

        $saferpayClient->refund($payment)->willReturn($refundResponse);
        $refundResponse->isSuccessful()->willReturn(true);
        $refundResponse->getTransaction()->willReturn($transaction);
        $transaction->getId()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        $payment
            ->setDetails([
                'transaction_id' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
            ])
            ->shouldBeCalled()
        ;

        $saferpayClient->capture($payment)->willReturn($captureResponse);
        $captureResponse->isSuccessful()->willReturn(true);
        $captureResponse->getCaptureId()->willReturn('0d7OYrAInYCWSASdzSh3bbr4jrSb_c');

        $payment
            ->setDetails([
                'status' => StatusAction::STATUS_REFUNDED,
                'capture_id' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c',
            ])
            ->shouldBeCalled()
        ;

        $this->execute($request->getWrappedObject());
    }

    function it_throws_the_payment_refund_failed_exception_if_there_is_different_status_code_than_ok_after_authorizing_the_refund(
        SaferpayClientInterface $saferpayClient,
        SyliusPaymentInterface $payment,
        RefundInterface $request,
        TokenInterface $token,
        RefundResponse $refundResponse,
    ): void {
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);

        $saferpayClient->refund($payment)->willReturn($refundResponse);
        $refundResponse->isSuccessful()->willReturn(false);

        $this
            ->shouldThrow(PaymentRefundFailedException::class)
            ->during('execute', [$request->getWrappedObject()])
        ;
    }

    function it_throws_the_payment_refund_failed_exception_if_there_is_different_status_code_than_ok_after_capturing_the_refund(
        SaferpayClientInterface $saferpayClient,
        SyliusPaymentInterface $payment,
        RefundInterface $request,
        TokenInterface $token,
        RefundResponse $refundResponse,
        Transaction $transaction,
        CaptureResponse $captureResponse,
    ): void {
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);

        $payment->getDetails()->willReturn([]);

        $saferpayClient->refund($payment)->willReturn($refundResponse);
        $refundResponse->isSuccessful()->willReturn(true);
        $refundResponse->getTransaction()->willReturn($transaction);
        $transaction->getId()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        $payment
            ->setDetails([
                'transaction_id' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
            ])
            ->shouldBeCalled()
        ;

        $saferpayClient->capture($payment)->willReturn($captureResponse);
        $captureResponse->isSuccessful()->willReturn(false);

        $this
            ->shouldThrow(PaymentRefundFailedException::class)
            ->during('execute', [$request->getWrappedObject()])
        ;
    }
}
