<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\CaptureAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusCheckerInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class CaptureActionTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<SaferpayClientInterface> */
    private ObjectProphecy $saferpayClient;

    /** @var ObjectProphecy<StatusCheckerInterface> */
    private ObjectProphecy $statusChecker;

    private CaptureAction $action;

    protected function setUp(): void
    {
        $this->saferpayClient = $this->prophesize(SaferpayClientInterface::class);
        $this->statusChecker = $this->prophesize(StatusCheckerInterface::class);

        $this->action = new CaptureAction($this->saferpayClient->reveal(), $this->statusChecker->reveal());
    }

    public function test_it_supports_capture_request_and_payment_model(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = new Capture($payment->reveal());

        self::assertTrue($this->action->supports($request));
    }

    public function test_it_does_not_support_other_requests_than_capture(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = new Authorize($payment->reveal());

        self::assertFalse($this->action->supports($request));
    }

    public function test_it_does_not_support_capture_request_with_wrong_model(): void
    {
        $payment = $this->prophesize(PayumPaymentInterface::class);
        $request = new Capture($payment->reveal());

        self::assertFalse($this->action->supports($request));
    }

    public function test_it_throws_an_exception_when_request_not_supported_on_execute(): void
    {
        $this->expectException(RequestNotSupportedException::class);

        $this->action->execute(new Authorize(new \stdClass()));
    }

    public function test_it_does_nothing_if_payment_has_captured_status(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);

        $this->statusChecker->isCaptured($payment->reveal())->willReturn(true);

        $this->saferpayClient->capture($payment->reveal())->shouldNotBeCalled();
        $payment->setDetails(Argument::any())->shouldNotBeCalled();

        $this->action->execute(new Capture($payment->reveal()));
    }

    public function test_it_captures_the_payment(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $captureResponse = $this->prophesize(CaptureResponse::class);

        $this->statusChecker->isCaptured($payment->reveal())->willReturn(false);

        $this->saferpayClient->capture($payment->reveal())->willReturn($captureResponse->reveal());
        $captureResponse->getStatus()->willReturn(StatusAction::STATUS_CAPTURED);
        $captureResponse->getCaptureId()->willReturn('0d7OYrAInYCWSASdzSh3bbr4jrSb_c');
        $captureResponse->getStatusCode()->willReturn(200);

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn([]);
        $payment
            ->setDetails(['status' => StatusAction::STATUS_CAPTURED, 'capture_id' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c'])
            ->shouldBeCalled()
        ;

        $this->action->execute(new Capture($payment->reveal()));
    }

    public function test_it_marks_the_payment_as_failed_if_there_is_different_status_code_than_ok(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $errorResponse = $this->prophesize(ErrorResponse::class);

        $this->statusChecker->isCaptured($payment->reveal())->willReturn(false);

        $this->saferpayClient->capture($payment->reveal())->willReturn($errorResponse->reveal());
        $errorResponse->getTransactionId()->willReturn('TRANSACTION_ID');
        $errorResponse->getStatusCode()->willReturn(402);

        $payment->getDetails()->willReturn([]);
        $payment
            ->setDetails(['status' => StatusAction::STATUS_FAILED, 'transaction_id' => 'TRANSACTION_ID'])
            ->shouldBeCalled()
        ;

        $this->action->execute(new Capture($payment->reveal()));
    }
}
