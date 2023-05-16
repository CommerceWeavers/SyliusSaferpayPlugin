<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\Handler;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\AssertPaymentCommand;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\CapturePaymentCommand;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\CaptureFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\ResolveNextCommandFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\ResolveNextCommandInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

final class CapturePaymentHandlerSpec extends ObjectBehavior
{
    function let(
        MessageBusInterface $commandBus,
        Payum $payum,
        StorageInterface $tokenStorage,
        CaptureFactoryInterface $captureFactory,
        GetStatusFactoryInterface $getStatusRequestFactory,
        ResolveNextCommandFactoryInterface $resolveNextCommandFactory,
    ): void {
        $this->beConstructedWith(
            $commandBus,
            $payum,
            $tokenStorage,
            $captureFactory,
            $getStatusRequestFactory,
            $resolveNextCommandFactory
        );
    }

    function it_should_throw_an_exception_if_the_token_is_not_found(StorageInterface $tokenStorage): void
    {
        $tokenStorage->find('token')->willReturn(null);

        $this->shouldThrow(\InvalidArgumentException::class)->during('__invoke', [new CapturePaymentCommand('token')]);
    }

    function it_executes_capture_flow_and_dispatches_command_retrieved_from_resolve_next_command_action(
        MessageBusInterface $commandBus,
        Payum $payum,
        StorageInterface $tokenStorage,
        CaptureFactoryInterface $captureFactory,
        GetStatusFactoryInterface $getStatusRequestFactory,
        ResolveNextCommandFactoryInterface $resolveNextCommandFactory,
        GatewayInterface $gateway,
        TokenInterface $captureToken,
        Capture $capture,
        GetStatusInterface $getStatus,
        ResolveNextCommandInterface $resolveNextCommand,
    ): void {
        $tokenStorage->find('capture_token')->willReturn($captureToken);

        $captureToken->getGatewayName()->willReturn('saferpay');
        $payum->getGateway('saferpay')->willReturn($gateway);

        $captureFactory->createNewWithModel($captureToken)->willReturn($capture);
        $gateway->execute($capture)->shouldBeCalled();

        $capture->getFirstModel()->willReturn($captureToken);
        $getStatusRequestFactory->createNewWithModel($captureToken)->willReturn($getStatus);
        $gateway->execute($getStatus)->shouldBeCalled();

        $tokenStorage->delete($captureToken)->shouldBeCalled();

        $resolveNextCommandFactory->createNewWithModel($captureToken)->willReturn($resolveNextCommand);
        $resolveNextCommand->getNextCommand()->willReturn(new AssertPaymentCommand('next_token'));
        $gateway->execute($resolveNextCommand)->shouldBeCalled();

        $commandBus->dispatch(Argument::type(AssertPaymentCommand::class), [new DispatchAfterCurrentBusStamp()])
            ->willReturn(new Envelope(new \stdClass()))
            ->shouldBeCalled()
        ;

        $this->__invoke(new CapturePaymentCommand('capture_token'));
    }

    function it_executes_capture_flow_and_does_not_dispatch_command_retrieved_from_resolve_next_command_action_if_null(
        MessageBusInterface $commandBus,
        Payum $payum,
        StorageInterface $tokenStorage,
        CaptureFactoryInterface $captureFactory,
        GetStatusFactoryInterface $getStatusRequestFactory,
        ResolveNextCommandFactoryInterface $resolveNextCommandFactory,
        GatewayInterface $gateway,
        TokenInterface $captureToken,
        Capture $capture,
        GetStatusInterface $getStatus,
        ResolveNextCommandInterface $resolveNextCommand,
    ): void {
        $tokenStorage->find('capture_token')->willReturn($captureToken);

        $captureToken->getGatewayName()->willReturn('saferpay');
        $payum->getGateway('saferpay')->willReturn($gateway);

        $captureFactory->createNewWithModel($captureToken)->willReturn($capture);
        $gateway->execute($capture)->shouldBeCalled();

        $capture->getFirstModel()->willReturn($captureToken);
        $getStatusRequestFactory->createNewWithModel($captureToken)->willReturn($getStatus);
        $gateway->execute($getStatus)->shouldBeCalled();

        $tokenStorage->delete($captureToken)->shouldBeCalled();

        $resolveNextCommandFactory->createNewWithModel($captureToken)->willReturn($resolveNextCommand);
        $resolveNextCommand->getNextCommand()->willReturn(null);
        $gateway->execute($resolveNextCommand)->shouldBeCalled();

        $commandBus->dispatch()->shouldNotBeCalled();

        $this->__invoke(new CapturePaymentCommand('capture_token'));
    }
}
