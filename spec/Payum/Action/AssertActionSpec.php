<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Assert;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class AssertActionSpec extends ObjectBehavior
{
    function let(SaferpayClientInterface $saferpayClient): void
    {
        $this->beConstructedWith($saferpayClient);
    }

    function it_supports_assert_request_and_payment_model(SyliusPaymentInterface $payment): void
    {
        $request = new Assert($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(true);
    }

    function it_does_not_support_other_requests_than_assert(SyliusPaymentInterface $payment): void
    {
        $request = new Capture($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(false);
    }

    function it_does_not_support_assert_request_with_wrong_model(PayumPaymentInterface $payment): void
    {
        $request = new Assert($payment->getWrappedObject());

        $this->supports($request)->shouldReturn(false);
    }

    function it_throws_an_exception_when_request_not_supported_on_execute(): void
    {
        $this
            ->shouldThrow(RequestNotSupportedException::class)
            ->during('execute', [new Capture(new \stdClass())])
        ;
    }

    function it_asserts_the_payment(
        SaferpayClientInterface $saferpayClient,
        SyliusPaymentInterface $payment,
    ): void {
        $payment->getDetails()->willReturn([]);

        $saferpayClient->assert($payment)->willReturn([
            'Transaction' => [
                'Status' => StatusAction::STATUS_AUTHORIZED,
                'Id' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
            ],
        ]);

        $payment
            ->setDetails([
                'transaction_id' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'status' => StatusAction::STATUS_AUTHORIZED
            ])
            ->shouldBeCalled()
        ;

        $this->execute(new Assert($payment->getWrappedObject()));
    }
}
