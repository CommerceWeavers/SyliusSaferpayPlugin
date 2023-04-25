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
        TokenInterface $token,
    ): void {
        $payment->getId()->willReturn(1);
        $dateTimeProvider->now()->willReturn(new \DateTimeImmutable());

        $authorizeResponse->toArray()->willReturn([]);
        $authorizeResponse->isSuccessful()->willReturn(true);

        $client->authorize($payment, $token)->shouldBeCalled()->willReturn($authorizeResponse);

        $eventBus
            ->dispatch(Argument::type(SaferpayPaymentEvent::class))
            ->shouldBeCalled()
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $result = $this->authorize($payment, $token);
        $result->shouldBe($authorizeResponse);
    }

    function it_dispatches_saferpay_payment_event_on_assert(
        SaferpayClientInterface $client,
        MessageBusInterface $eventBus,
        DateTimeProviderInterface $dateTimeProvider,
        PaymentInterface $payment,
        AssertResponse $assertResponse,
    ): void {
        $payment->getId()->willReturn(1);
        $dateTimeProvider->now()->willReturn(new \DateTimeImmutable());

        $assertResponse->toArray()->willReturn([]);
        $assertResponse->isSuccessful()->willReturn(true);

        $client->assert($payment)->shouldBeCalled()->willReturn($assertResponse);

        $eventBus
            ->dispatch(Argument::type(SaferpayPaymentEvent::class))
            ->shouldBeCalled()
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $result = $this->assert($payment);
        $result->shouldBe($assertResponse);
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

        $captureResponse->toArray()->willReturn([]);
        $captureResponse->isSuccessful()->willReturn(true);

        $client->capture($payment)->shouldBeCalled()->willReturn($captureResponse);

        $eventBus
            ->dispatch(Argument::type(SaferpayPaymentEvent::class))
            ->shouldBeCalled()
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $result = $this->capture($payment);
        $result->shouldBe($captureResponse);
    }
}
