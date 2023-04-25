<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Assert;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
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

    function it_asserts_the_successfull_payment(
        SaferpayClientInterface $saferpayClient,
        SyliusPaymentInterface $payment,
        AssertResponse $assertResponse,
        AssertResponse\Transaction $transaction,
    ): void {
        $payment->getDetails()->willReturn([]);

        $transaction->getStatus()->willReturn(StatusAction::STATUS_AUTHORIZED);
        $transaction->getId()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');
        $assertResponse->getStatusCode()->willReturn(200);
        $assertResponse->getTransaction()->willReturn($transaction);
        $saferpayClient->assert($payment)->willReturn($assertResponse);

        $payment
            ->setDetails([
                'transaction_id' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'status' => StatusAction::STATUS_AUTHORIZED
            ])
            ->shouldBeCalled()
        ;

        $this->execute(new Assert($payment->getWrappedObject()));
    }

    function it_asserts_the_unsuccessful_payment(
        SaferpayClientInterface $saferpayClient,
        SyliusPaymentInterface $payment,
        AssertResponse $assertResponse,
        AssertResponse\Error $error,
    ): void {
        $payment->getDetails()->willReturn([]);

        $assertResponse->getStatusCode()->willReturn(402);
        $assertResponse->getError()->willReturn($error);
        $error->getTransactionId()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');
        $saferpayClient->assert($payment)->willReturn($assertResponse);

        $payment
            ->setDetails([
                'transaction_id' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'status' => StatusAction::STATUS_FAILED
            ])
            ->shouldBeCalled()
        ;

        $this->execute(new Assert($payment->getWrappedObject()));
    }
}
