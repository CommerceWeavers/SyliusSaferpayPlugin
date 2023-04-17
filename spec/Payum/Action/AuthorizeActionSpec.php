<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class AuthorizeActionSpec extends ObjectBehavior
{
    function let(SaferpayClientInterface $saferpayClient): void
    {
        $this->beConstructedWith($saferpayClient);
    }

    function it_supports_authorize_request_and_payment_model(SyliusPaymentInterface $payment): void
    {
        $request = new Authorize($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(true);
    }

    function it_does_not_support_other_requests_than_authorize(SyliusPaymentInterface $payment): void
    {
        $request = new Capture($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(false);
    }

    function it_does_not_support_authorize_request_with_wrong_model(PayumPaymentInterface $payment): void
    {
        $request = new Authorize($payment->getWrappedObject());

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

    function it_authorizes_the_payment(
        SaferpayClientInterface $saferpayClient,
        SyliusPaymentInterface $payment,
        Authorize $request,
        TokenInterface $token,
    ): void {
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);

        $saferpayClient->authorize($payment, $token)->willReturn([
            'ResponseHeader' => [
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
            ],
            'Token' => 'TOKEN',
            'RedirectUrl' => 'https://example.com/after',
        ]);

        $payment
            ->setDetails([
                'request_id' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'saferpay_token' => 'TOKEN',
                'status' => StatusAction::STATUS_NEW
            ])
            ->shouldBeCalled()
        ;

        $this->execute($request->getWrappedObject());
    }
}
