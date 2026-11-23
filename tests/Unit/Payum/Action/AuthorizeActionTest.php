<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\AuthorizeAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class AuthorizeActionTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<SaferpayClientInterface> */
    private ObjectProphecy $saferpayClient;

    private AuthorizeAction $action;

    protected function setUp(): void
    {
        $this->saferpayClient = $this->prophesize(SaferpayClientInterface::class);

        $this->action = new AuthorizeAction($this->saferpayClient->reveal());
    }

    public function test_it_supports_authorize_request_and_payment_model(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = new Authorize($payment->reveal());

        self::assertTrue($this->action->supports($request));
    }

    public function test_it_does_not_support_other_requests_than_authorize(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = new Capture($payment->reveal());

        self::assertFalse($this->action->supports($request));
    }

    public function test_it_does_not_support_authorize_request_with_wrong_model(): void
    {
        $payment = $this->prophesize(PayumPaymentInterface::class);
        $request = new Authorize($payment->reveal());

        self::assertFalse($this->action->supports($request));
    }

    public function test_it_throws_an_exception_when_request_not_supported_on_execute(): void
    {
        $this->expectException(RequestNotSupportedException::class);

        $this->action->execute(new Capture(new \stdClass()));
    }

    public function test_it_throws_an_exception_when_request_has_no_token(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = $this->prophesize(Authorize::class);

        $request->getModel()->willReturn($payment->reveal());
        $request->getToken()->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        $this->action->execute($request->reveal());
    }

    public function test_it_does_nothing_when_the_payment_states_is_different_than_new(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = $this->prophesize(Authorize::class);
        $token = $this->prophesize(TokenInterface::class);

        $request->getModel()->willReturn($payment->reveal());
        $request->getToken()->willReturn($token->reveal());

        $payment->getState()->willReturn(SyliusPaymentInterface::STATE_COMPLETED);

        $this->saferpayClient->authorize($payment->reveal(), $token->reveal())->shouldNotBeCalled();

        $this->action->execute($request->reveal());
    }

    public function test_it_authorizes_the_payment(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = $this->prophesize(Authorize::class);
        $token = $this->prophesize(TokenInterface::class);
        $responseHeader = $this->prophesize(ResponseHeader::class);
        $authorizeResponse = $this->prophesize(AuthorizeResponse::class);

        $request->getModel()->willReturn($payment->reveal());
        $request->getToken()->willReturn($token->reveal());

        $payment->getState()->willReturn(SyliusPaymentInterface::STATE_NEW);

        $this->saferpayClient->authorize($payment->reveal(), $token->reveal())->willReturn($authorizeResponse->reveal());
        $authorizeResponse->getToken()->willReturn('TOKEN');
        $authorizeResponse->getRedirectUrl()->willReturn('https://example.com/after');
        $authorizeResponse->getStatusCode()->willReturn(200);
        $authorizeResponse->getResponseHeader()->willReturn($responseHeader->reveal());
        $responseHeader->getRequestId()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn([]);
        $payment
            ->setDetails([
                'request_id' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'saferpay_token' => 'TOKEN',
                'status' => StatusAction::STATUS_NEW,
            ])
            ->shouldBeCalled()
        ;

        $this->action->execute($request->reveal());
    }

    public function test_it_marks_the_payment_as_failed_if_there_is_different_status_code_than_ok(): void
    {
        $payment = $this->prophesize(SyliusPaymentInterface::class);
        $request = $this->prophesize(Authorize::class);
        $token = $this->prophesize(TokenInterface::class);
        $errorResponse = $this->prophesize(ErrorResponse::class);

        $request->getModel()->willReturn($payment->reveal());
        $request->getToken()->willReturn($token->reveal());

        $payment->getState()->willReturn(SyliusPaymentInterface::STATE_NEW);

        $this->saferpayClient->authorize($payment->reveal(), $token->reveal())->willReturn($errorResponse->reveal());
        $errorResponse->getStatusCode()->willReturn(402);

        $payment->getDetails()->willReturn(['processing' => true]);
        $payment
            ->setDetails(['processing' => true, 'status' => StatusAction::STATUS_FAILED])
            ->shouldBeCalled()
        ;

        $this->action->execute($request->reveal());
    }
}
