<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Transaction;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\RefundAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\PaymentRefundFailedException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Refund;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\RefundInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class RefundActionTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<SaferpayClientInterface> */
    private ObjectProphecy $saferpayClient;

    private RefundAction $action;

    protected function setUp(): void
    {
        $this->saferpayClient = $this->prophesize(SaferpayClientInterface::class);

        $this->action = new RefundAction($this->saferpayClient->reveal());
    }

    public function test_it_supports_refund_request_and_payment_model(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = new Refund($payment->reveal());

        self::assertTrue($this->action->supports($request));
    }

    public function test_it_does_not_support_other_requests_than_refund(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = new Capture($payment->reveal());

        self::assertFalse($this->action->supports($request));
    }

    public function test_it_does_not_support_refund_request_with_wrong_model(): void
    {
        $payment = $this->prophesize(PayumPaymentInterface::class);
        $request = new Refund($payment->reveal());

        self::assertFalse($this->action->supports($request));
    }

    public function test_it_throws_an_exception_when_request_not_supported_on_execute(): void
    {
        $this->expectException(RequestNotSupportedException::class);

        $this->action->execute(new Capture(new \stdClass()));
    }

    public function test_it_rejects_a_non_refund_request_for_a_sylius_payment_without_calling_saferpay(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = $this->prophesize(Authorize::class);
        $request->getModel()->willReturn($payment->reveal());

        $this->saferpayClient->refund(Argument::cetera())->shouldNotBeCalled();
        $this->saferpayClient->capture(Argument::cetera())->shouldNotBeCalled();

        $this->expectException(RequestNotSupportedException::class);

        $this->action->execute($request->reveal());
    }

    public function test_it_refunds_the_payment(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = $this->prophesize(RefundInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $refundResponse = $this->prophesize(RefundResponse::class);
        $transaction = $this->prophesize(Transaction::class);
        $captureResponse = $this->prophesize(CaptureResponse::class);

        $request->getModel()->willReturn($payment->reveal());
        $request->getToken()->willReturn($token->reveal());

        $payment->getDetails()->willReturn([]);

        $this->saferpayClient->refund($payment->reveal())->willReturn($refundResponse->reveal());
        $refundResponse->isSuccessful()->willReturn(true);
        $refundResponse->getTransaction()->willReturn($transaction->reveal());
        $transaction->getId()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');
        $transaction->getStatus()->willReturn('AUTHORIZED');

        $payment
            ->setDetails([
                'transaction_id' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
            ])
            ->shouldBeCalled()
        ;

        $this->saferpayClient->capture($payment->reveal())->willReturn($captureResponse->reveal());
        $captureResponse->isSuccessful()->willReturn(true);
        $captureResponse->getCaptureId()->willReturn('0d7OYrAInYCWSASdzSh3bbr4jrSb_c');

        $payment
            ->setDetails([
                'status' => StatusAction::STATUS_REFUNDED,
                'capture_id' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c',
            ])
            ->shouldBeCalled()
        ;

        $this->action->execute($request->reveal());
    }

    public function test_it_does_not_capture_if_the_payment_has_been_captured_during_refund(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = $this->prophesize(RefundInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $refundResponse = $this->prophesize(RefundResponse::class);
        $transaction = $this->prophesize(Transaction::class);

        $request->getModel()->willReturn($payment->reveal());
        $request->getToken()->willReturn($token->reveal());

        $payment->getDetails()->willReturn([]);

        $this->saferpayClient->refund($payment->reveal())->willReturn($refundResponse->reveal());
        $refundResponse->isSuccessful()->willReturn(true);
        $refundResponse->getTransaction()->willReturn($transaction->reveal());
        $transaction->getStatus()->willReturn('CAPTURED');
        $transaction->getCaptureId()->willReturn('0d7OYrAInYCWSASdzSh3bbr4jrSb_c');

        $payment
            ->setDetails([
                'status' => StatusAction::STATUS_REFUNDED,
                'capture_id' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c',
            ])
            ->shouldBeCalled()
        ;

        $this->saferpayClient->capture($payment->reveal())->shouldNotBeCalled();

        $this->action->execute($request->reveal());
    }

    public function test_it_throws_the_payment_refund_failed_exception_if_there_is_different_status_code_than_ok_after_authorizing_the_refund(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = $this->prophesize(RefundInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $errorResponse = $this->prophesize(ErrorResponse::class);

        $request->getModel()->willReturn($payment->reveal());
        $request->getToken()->willReturn($token->reveal());

        $this->saferpayClient->refund($payment->reveal())->willReturn($errorResponse->reveal());

        $this->expectException(PaymentRefundFailedException::class);

        $this->action->execute($request->reveal());
    }

    public function test_it_throws_the_payment_refund_failed_exception_if_there_is_different_status_code_than_ok_after_capturing_the_refund(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = $this->prophesize(RefundInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $refundResponse = $this->prophesize(RefundResponse::class);
        $transaction = $this->prophesize(Transaction::class);
        $errorResponse = $this->prophesize(ErrorResponse::class);

        $request->getModel()->willReturn($payment->reveal());
        $request->getToken()->willReturn($token->reveal());

        $payment->getDetails()->willReturn([]);

        $this->saferpayClient->refund($payment->reveal())->willReturn($refundResponse->reveal());
        $refundResponse->isSuccessful()->willReturn(true);
        $refundResponse->getTransaction()->willReturn($transaction->reveal());
        $transaction->getId()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');
        $transaction->getStatus()->willReturn('AUTHORIZED');

        $payment
            ->setDetails([
                'transaction_id' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
            ])
            ->shouldBeCalled()
        ;

        $this->saferpayClient->capture($payment->reveal())->willReturn($errorResponse->reveal());

        $this->expectException(PaymentRefundFailedException::class);

        $this->action->execute($request->reveal());
    }
}
