<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\Handler;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\AssertPaymentCommand;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\AssertFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\ResolveNextCommandFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\AssertInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\ResolveNextCommandInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

final class AssertPaymentHandlerSpec extends ObjectBehavior
{
    function let(
        MessageBusInterface $commandBus,
        Payum $payum,
        StorageInterface $tokenStorage,
        AssertFactoryInterface $assertFactory,
        GetStatusFactoryInterface $getStatusRequestFactory,
        ResolveNextCommandFactoryInterface $resolveNextCommandFactory,
    ): void {
        $this->beConstructedWith(
            $commandBus,
            $payum,
            $tokenStorage,
            $assertFactory,
            $getStatusRequestFactory,
            $resolveNextCommandFactory
        );
    }

    function it_throws_an_exception_if_the_token_is_not_found(StorageInterface $tokenStorage): void
    {
        $tokenStorage->find('token')->willReturn(null);

        $this->shouldThrow(\InvalidArgumentException::class)->during('__invoke', [new AssertPaymentCommand('token')]);
    }

    function it_executes_assert_flow_and_dispatches_command_retrieved_from_resolve_next_command_action(
        MessageBusInterface $commandBus,
        Payum $payum,
        StorageInterface $tokenStorage,
        AssertFactoryInterface $assertFactory,
        GetStatusFactoryInterface $getStatusRequestFactory,
        ResolveNextCommandFactoryInterface $resolveNextCommandFactory,
        GatewayInterface $gateway,
        TokenInterface $assertToken,
        AssertInterface $assert,
        GetStatusInterface $getStatus,
        ResolveNextCommandInterface $resolveNextCommand,
    ): void {
        $tokenStorage->find('assert_token')->willReturn($assertToken);

        $assertToken->getGatewayName()->willReturn('saferpay');
        $payum->getGateway('saferpay')->willReturn($gateway);

        $assertFactory->createNewWithModel($assertToken)->willReturn($assert);
        $gateway->execute($assert)->shouldBeCalled();

        $assert->getFirstModel()->willReturn(new \stdClass());
        $getStatusRequestFactory->createNewWithModel(new \stdClass())->willReturn($getStatus);
        $gateway->execute($getStatus)->shouldBeCalled();

        $tokenStorage->delete($assertToken)->shouldBeCalled();

        $resolveNextCommandFactory->createNewWithModel(new \stdClass())->willReturn($resolveNextCommand);
        $resolveNextCommand->getNextCommand()->willReturn(new AssertPaymentCommand('next_token'));
        $gateway->execute($resolveNextCommand)->shouldBeCalled();

        $commandBus->dispatch(Argument::type(AssertPaymentCommand::class), [new DispatchAfterCurrentBusStamp()])
            ->willReturn(new Envelope(new \stdClass()))
            ->shouldBeCalled()
        ;

        $this(new AssertPaymentCommand('assert_token'));
    }

    function it_executes_assert_flow_and_does_not_dispatch_command_retrieved_from_resolve_next_command_action_if_null(
        MessageBusInterface $commandBus,
        Payum $payum,
        StorageInterface $tokenStorage,
        AssertFactoryInterface $assertFactory,
        GetStatusFactoryInterface $getStatusRequestFactory,
        ResolveNextCommandFactoryInterface $resolveNextCommandFactory,
        GatewayInterface $gateway,
        TokenInterface $assertToken,
        AssertInterface $assert,
        GetStatusInterface $getStatus,
        ResolveNextCommandInterface $resolveNextCommand,
    ): void {
        $tokenStorage->find('assert_token')->willReturn($assertToken);

        $assertToken->getGatewayName()->willReturn('saferpay');
        $payum->getGateway('saferpay')->willReturn($gateway);

        $assertFactory->createNewWithModel($assertToken)->willReturn($assert);
        $gateway->execute($assert)->shouldBeCalled();

        $assert->getFirstModel()->willReturn(new \stdClass());
        $getStatusRequestFactory->createNewWithModel(new \stdClass())->willReturn($getStatus);
        $gateway->execute($getStatus)->shouldBeCalled();

        $tokenStorage->delete($assertToken)->shouldBeCalled();

        $resolveNextCommandFactory->createNewWithModel(new \stdClass())->willReturn($resolveNextCommand);
        $resolveNextCommand->getNextCommand()->willReturn(null);
        $gateway->execute($resolveNextCommand)->shouldBeCalled();

        $commandBus->dispatch()->shouldNotBeCalled();

        $this(new AssertPaymentCommand('assert_token'));
    }
}
