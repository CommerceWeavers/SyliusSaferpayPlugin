<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusMarkerInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Authorize;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class StatusActionTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<StatusMarkerInterface> */
    private ObjectProphecy $statusMarker;

    /** @var ObjectProphecy<GetStatus> */
    private ObjectProphecy $request;

    private StatusAction $action;

    protected function setUp(): void
    {
        $this->statusMarker = $this->prophesize(StatusMarkerInterface::class);
        $this->request = $this->prophesize(GetStatus::class);

        $this->action = new StatusAction($this->statusMarker->reveal());
    }

    public function test_it_supports_get_status_request_and_payment_model(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = new GetStatus($payment->reveal());

        self::assertTrue($this->action->supports($request));
    }

    public function test_it_does_not_support_other_requests_than_get_status(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = new Authorize($payment->reveal());

        self::assertFalse($this->action->supports($request));
    }

    public function test_it_does_not_support_get_status_request_with_wrong_model(): void
    {
        $payumPayment = $this->prophesize(PayumPaymentInterface::class);
        $request = new GetStatus($payumPayment->reveal());

        self::assertFalse($this->action->supports($request));
    }

    public function test_it_throws_an_exception_when_request_not_supported_on_execute(): void
    {
        $this->expectException(RequestNotSupportedException::class);

        $this->action->execute(new Authorize(new \stdClass()));
    }

    public function test_it_marks_new_on_request_when_payment_status_is_new(): void
    {
        $request = $this->request->reveal();
        $this->request->getFirstModel()->willReturn($this->prophesize(SyliusPaymentInterface::class)->reveal());

        $this->statusMarker->canBeMarkedAsNew($request)->willReturn(true);
        $this->statusMarker->markAsNew($request)->shouldBeCalled();

        $this->action->execute($request);
    }

    public function test_it_marks_authorized_on_request_when_payment_status_is_authorized(): void
    {
        $request = $this->request->reveal();
        $this->request->getFirstModel()->willReturn($this->prophesize(SyliusPaymentInterface::class)->reveal());

        $this->statusMarker->canBeMarkedAsNew($request)->willReturn(false);
        $this->statusMarker->markAsNew($request)->shouldNotBeCalled();
        $this->statusMarker->canBeMarkedAsAuthorized($request)->willReturn(true);
        $this->statusMarker->markAsAuthorized($request)->shouldBeCalled();

        $this->action->execute($request);
    }

    public function test_it_marks_captured_on_request_when_payment_status_is_captured(): void
    {
        $request = $this->request->reveal();
        $this->request->getFirstModel()->willReturn($this->prophesize(SyliusPaymentInterface::class)->reveal());

        $this->statusMarker->canBeMarkedAsNew($request)->willReturn(false);
        $this->statusMarker->markAsNew($request)->shouldNotBeCalled();
        $this->statusMarker->canBeMarkedAsAuthorized($request)->willReturn(false);
        $this->statusMarker->markAsAuthorized($request)->shouldNotBeCalled();
        $this->statusMarker->canBeMarkedAsCaptured($request)->willReturn(true);
        $this->statusMarker->markAsCaptured($request)->shouldBeCalled();

        $this->action->execute($request);
    }

    public function test_it_marks_cancelled_on_request_when_payment_status_is_captured(): void
    {
        $request = $this->request->reveal();
        $this->request->getFirstModel()->willReturn($this->prophesize(SyliusPaymentInterface::class)->reveal());

        $this->statusMarker->canBeMarkedAsNew($request)->willReturn(false);
        $this->statusMarker->markAsNew($request)->shouldNotBeCalled();
        $this->statusMarker->canBeMarkedAsAuthorized($request)->willReturn(false);
        $this->statusMarker->markAsAuthorized($request)->shouldNotBeCalled();
        $this->statusMarker->canBeMarkedAsCaptured($request)->willReturn(false);
        $this->statusMarker->markAsCaptured($request)->shouldNotBeCalled();
        $this->statusMarker->canBeMarkedAsCancelled($request)->willReturn(true);
        $this->statusMarker->markAsCancelled($request)->shouldBeCalled();

        $this->action->execute($request);
    }

    public function test_it_marks_failed_on_request_when_payment_status_cannot_be_matched(): void
    {
        $request = $this->request->reveal();
        $this->request->getFirstModel()->willReturn($this->prophesize(SyliusPaymentInterface::class)->reveal());

        $this->statusMarker->canBeMarkedAsNew($request)->willReturn(false);
        $this->statusMarker->markAsNew($request)->shouldNotBeCalled();
        $this->statusMarker->canBeMarkedAsAuthorized($request)->willReturn(false);
        $this->statusMarker->markAsAuthorized($request)->shouldNotBeCalled();
        $this->statusMarker->canBeMarkedAsCaptured($request)->willReturn(false);
        $this->statusMarker->markAsCaptured($request)->shouldNotBeCalled();
        $this->statusMarker->canBeMarkedAsCancelled($request)->willReturn(false);
        $this->statusMarker->markAsCancelled($request)->shouldNotBeCalled();
        $this->statusMarker->markAsFailed($request)->shouldBeCalled();

        $this->action->execute($request);
    }
}
