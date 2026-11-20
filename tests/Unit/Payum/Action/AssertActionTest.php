<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Transaction;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Assert\FailedResponseHandlerInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Assert\SuccessfulResponseHandlerInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\AssertAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\ErrorName;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Assert;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class AssertActionTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<SaferpayClientInterface> */
    private ObjectProphecy $saferpayClient;

    /** @var ObjectProphecy<SuccessfulResponseHandlerInterface> */
    private ObjectProphecy $successfulResponseHandler;

    /** @var ObjectProphecy<FailedResponseHandlerInterface> */
    private ObjectProphecy $failedResponseHandler;

    private AssertAction $action;

    protected function setUp(): void
    {
        $this->saferpayClient = $this->prophesize(SaferpayClientInterface::class);
        $this->successfulResponseHandler = $this->prophesize(SuccessfulResponseHandlerInterface::class);
        $this->failedResponseHandler = $this->prophesize(FailedResponseHandlerInterface::class);

        $this->action = new AssertAction(
            $this->saferpayClient->reveal(),
            $this->successfulResponseHandler->reveal(),
            $this->failedResponseHandler->reveal(),
        );
    }

    public function test_it_supports_assert_request_and_payment_model(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = new Assert($payment->reveal());

        self::assertTrue($this->action->supports($request));
    }

    public function test_it_does_not_support_other_requests_than_assert(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = new Capture($payment->reveal());

        self::assertFalse($this->action->supports($request));
    }

    public function test_it_does_not_support_assert_request_with_wrong_model(): void
    {
        $payment = $this->prophesize(PayumPaymentInterface::class);
        $request = new Assert($payment->reveal());

        self::assertFalse($this->action->supports($request));
    }

    public function test_it_throws_an_exception_when_request_not_supported_on_execute(): void
    {
        $this->expectException(RequestNotSupportedException::class);

        $this->action->execute(new Capture(new \stdClass()));
    }

    public function test_it_asserts_the_successful_payment(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $assertResponse = $this->prophesize(AssertResponse::class);
        $transaction = $this->prophesize(Transaction::class);

        $payment->getDetails()->willReturn([]);

        $transaction->getStatus()->willReturn(StatusAction::STATUS_AUTHORIZED);
        $transaction->getId()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');
        $assertResponse->getStatusCode()->willReturn(200);
        $assertResponse->getTransaction()->willReturn($transaction->reveal());
        $this->saferpayClient->assert($payment->reveal())->willReturn($assertResponse->reveal());

        $this->successfulResponseHandler->handle($payment->reveal(), $assertResponse->reveal())->shouldBeCalled();

        $this->action->execute(new Assert($payment->reveal()));
    }

    public function test_it_asserts_the_cancelled_payment(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $errorResponse = $this->prophesize(ErrorResponse::class);

        $payment->getDetails()->willReturn([]);

        $this->saferpayClient->assert($payment->reveal())->willReturn($errorResponse->reveal());
        $errorResponse->getStatusCode()->willReturn(402);
        $errorResponse->getName()->willReturn(ErrorName::TRANSACTION_ABORTED);
        $errorResponse->getTransactionId()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        $this->failedResponseHandler->handle($payment->reveal(), $errorResponse->reveal())->shouldBeCalled();

        $this->action->execute(new Assert($payment->reveal()));
    }

    public function test_it_asserts_the_failed_payment(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $errorResponse = $this->prophesize(ErrorResponse::class);

        $payment->getDetails()->willReturn([]);

        $this->saferpayClient->assert($payment->reveal())->willReturn($errorResponse->reveal());
        $errorResponse->getStatusCode()->willReturn(402);
        $errorResponse->getName()->willReturn(ErrorName::TRANSACTION_DECLINED);
        $errorResponse->getTransactionId()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        $this->failedResponseHandler->handle($payment->reveal(), $errorResponse->reveal())->shouldBeCalled();

        $this->action->execute(new Assert($payment->reveal()));
    }
}
