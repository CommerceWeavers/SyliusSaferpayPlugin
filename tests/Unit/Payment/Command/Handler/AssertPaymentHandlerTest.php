<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payment\Command\Handler;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\AssertPaymentCommand;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\Handler\AssertPaymentHandler;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\AssertFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\ResolveNextCommandFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\AssertInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\ResolveNextCommandInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

final class AssertPaymentHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<MessageBusInterface> */
    private ObjectProphecy $commandBus;

    /** @var ObjectProphecy<Payum> */
    private ObjectProphecy $payum;

    /** @var ObjectProphecy<StorageInterface> */
    private ObjectProphecy $tokenStorage;

    /** @var ObjectProphecy<AssertFactoryInterface> */
    private ObjectProphecy $assertFactory;

    /** @var ObjectProphecy<GetStatusFactoryInterface> */
    private ObjectProphecy $getStatusRequestFactory;

    /** @var ObjectProphecy<ResolveNextCommandFactoryInterface> */
    private ObjectProphecy $resolveNextCommandFactory;

    private AssertPaymentHandler $handler;

    protected function setUp(): void
    {
        $this->commandBus = $this->prophesize(MessageBusInterface::class);
        $this->payum = $this->prophesize(Payum::class);
        $this->tokenStorage = $this->prophesize(StorageInterface::class);
        $this->assertFactory = $this->prophesize(AssertFactoryInterface::class);
        $this->getStatusRequestFactory = $this->prophesize(GetStatusFactoryInterface::class);
        $this->resolveNextCommandFactory = $this->prophesize(ResolveNextCommandFactoryInterface::class);

        $this->handler = new AssertPaymentHandler(
            $this->commandBus->reveal(),
            $this->payum->reveal(),
            $this->tokenStorage->reveal(),
            $this->assertFactory->reveal(),
            $this->getStatusRequestFactory->reveal(),
            $this->resolveNextCommandFactory->reveal(),
        );
    }

    public function test_it_throws_an_exception_if_the_token_is_not_found(): void
    {
        $this->tokenStorage->find('token')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        ($this->handler)(new AssertPaymentCommand('token'));
    }

    public function test_it_executes_assert_flow_and_dispatches_command_retrieved_from_resolve_next_command_action(): void
    {
        $resolveNextCommand = $this->mockAssertFlow();
        $resolveNextCommand->getNextCommand()->willReturn(new AssertPaymentCommand('next_token'));

        $this->commandBus
            ->dispatch(Argument::type(AssertPaymentCommand::class), [new DispatchAfterCurrentBusStamp()])
            ->willReturn(new Envelope(new \stdClass()))
            ->shouldBeCalled()
        ;

        ($this->handler)(new AssertPaymentCommand('assert_token'));
    }

    public function test_it_executes_assert_flow_and_does_not_dispatch_command_retrieved_from_resolve_next_command_action_if_null(): void
    {
        $resolveNextCommand = $this->mockAssertFlow();
        $resolveNextCommand->getNextCommand()->willReturn(null);

        $this->commandBus->dispatch(Argument::cetera())->shouldNotBeCalled();

        ($this->handler)(new AssertPaymentCommand('assert_token'));
    }

    /** @return ObjectProphecy<ResolveNextCommandInterface> */
    private function mockAssertFlow(): ObjectProphecy
    {
        $gateway = $this->prophesize(GatewayInterface::class);
        $assertToken = $this->prophesize(TokenInterface::class);
        $assert = $this->prophesize(AssertInterface::class);
        $getStatus = $this->prophesize(GetStatusInterface::class);
        $resolveNextCommand = $this->prophesize(ResolveNextCommandInterface::class);

        $this->tokenStorage->find('assert_token')->willReturn($assertToken->reveal());

        $assertToken->getGatewayName()->willReturn('saferpay');
        $this->payum->getGateway('saferpay')->willReturn($gateway->reveal());

        $this->assertFactory->createNewWithModel($assertToken->reveal())->willReturn($assert->reveal());
        $gateway->execute($assert->reveal())->shouldBeCalled();

        $assert->getFirstModel()->willReturn(new \stdClass());
        $this->getStatusRequestFactory->createNewWithModel(new \stdClass())->willReturn($getStatus->reveal());
        $gateway->execute($getStatus->reveal())->shouldBeCalled();

        $this->tokenStorage->delete($assertToken->reveal())->shouldBeCalled();

        $this->resolveNextCommandFactory->createNewWithModel(new \stdClass())->willReturn($resolveNextCommand->reveal());
        $gateway->execute($resolveNextCommand->reveal())->shouldBeCalled();

        return $resolveNextCommand;
    }
}
