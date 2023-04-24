<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;
use CommerceWeavers\SyliusSaferpayPlugin\Event\SaferpayPaymentEvent;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Calendar\Provider\DateTimeProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class LoggableSaferpayClientSpec extends ObjectBehavior
{
    function let(
        SaferpayClientInterface $client,
        MessageBusInterface $eventBus,
        DateTimeProviderInterface $dateTimeProvider,
    ): void {
        $this->beConstructedWith($client, $eventBus, $dateTimeProvider);
    }

    function it_dispatches_saferpay_payment_event_on_authorize(
        SaferpayClientInterface   $client,
        MessageBusInterface $eventBus,
        DateTimeProviderInterface $dateTimeProvider,
        PaymentInterface $payment,
        AuthorizeResponse $authorizeResponse,
        ResponseHeader $responseHeader,
        TokenInterface $token,
    ): void {
        $payment->getId()->willReturn(1);

        $dateTimeProvider->now()->willReturn(new \DateTimeImmutable());

        $client->authorize($payment, $token)->shouldBeCalled()->willReturn($authorizeResponse);

        $responseHeader->getRequestId()->willReturn('REQUEST_ID')->shouldBeCalled();
        $authorizeResponse->getResponseHeader()->willReturn($responseHeader)->shouldBeCalled();
        $authorizeResponse->getToken()->willReturn('TOKEN')->shouldBeCalled();

        $eventBus
            ->dispatch(Argument::type(SaferpayPaymentEvent::class))
            ->shouldBeCalled()
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->authorize($payment, $token);
    }

    function it_dispatches_saferpay_payment_event_on_assert(
        SaferpayClientInterface $client,
        MessageBusInterface $eventBus,
        DateTimeProviderInterface $dateTimeProvider,
        PaymentInterface $payment,
        AssertResponse $assertResponse,
        ResponseHeader $responseHeader,
        AssertResponse\Transaction $transaction,
    ): void {
        $payment->getId()->willReturn(1);

        $dateTimeProvider->now()->willReturn(new \DateTimeImmutable());

        $client->assert($payment)->shouldBeCalled()->willReturn($assertResponse);

        $responseHeader->getRequestId()->willReturn('REQUEST_ID')->shouldBeCalled();
        $assertResponse->getResponseHeader()->willReturn($responseHeader)->shouldBeCalled();

        $transaction->getId()->willReturn('TRANSACTION_ID')->shouldBeCalled();
        $transaction->getStatus()->willReturn('SOME_STATUS')->shouldBeCalled();
        $assertResponse->getTransaction()->willReturn($transaction);

        $eventBus
            ->dispatch(Argument::type(SaferpayPaymentEvent::class))
            ->shouldBeCalled()
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->assert($payment);
    }

    function it_dispatches_saferpay_payment_event_on_capture(
        SaferpayClientInterface   $client,
        MessageBusInterface $eventBus,
        DateTimeProviderInterface $dateTimeProvider,
        PaymentInterface $payment,
        CaptureResponse $captureResponse,
        ResponseHeader $responseHeader,
    ): void {
        $payment->getId()->willReturn(1);

        $dateTimeProvider->now()->willReturn(new \DateTimeImmutable());

        $client->capture($payment)->shouldBeCalled()->willReturn($captureResponse);

        $responseHeader->getRequestId()->willReturn('REQUEST_ID')->shouldBeCalled();
        $captureResponse->getResponseHeader()->willReturn($responseHeader)->shouldBeCalled();
        $captureResponse->getCaptureId()->willReturn('CAPTURE_ID')->shouldBeCalled();
        $captureResponse->getStatus()->willReturn('CAPTURE_STATUS')->shouldBeCalled();

        $eventBus
            ->dispatch(Argument::type(SaferpayPaymentEvent::class))
            ->shouldBeCalled()
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->capture($payment);
    }
}
