<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payment\Command\Handler;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\AssertPaymentCommand;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\CapturePaymentCommand;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\Handler\CapturePaymentHandler;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\CaptureFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\ResolveNextCommandFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\ResolveNextCommandInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Capture;
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

final class CapturePaymentHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<MessageBusInterface> */
    private ObjectProphecy $commandBus;

    /** @var ObjectProphecy<Payum> */
    private ObjectProphecy $payum;

    /** @var ObjectProphecy<StorageInterface> */
    private ObjectProphecy $tokenStorage;

    /** @var ObjectProphecy<CaptureFactoryInterface> */
    private ObjectProphecy $captureFactory;

    /** @var ObjectProphecy<GetStatusFactoryInterface> */
    private ObjectProphecy $getStatusRequestFactory;

    /** @var ObjectProphecy<ResolveNextCommandFactoryInterface> */
    private ObjectProphecy $resolveNextCommandFactory;

    private CapturePaymentHandler $handler;

    protected function setUp(): void
    {
        $this->commandBus = $this->prophesize(MessageBusInterface::class);
        $this->payum = $this->prophesize(Payum::class);
        $this->tokenStorage = $this->prophesize(StorageInterface::class);
        $this->captureFactory = $this->prophesize(CaptureFactoryInterface::class);
        $this->getStatusRequestFactory = $this->prophesize(GetStatusFactoryInterface::class);
        $this->resolveNextCommandFactory = $this->prophesize(ResolveNextCommandFactoryInterface::class);

        $this->handler = new CapturePaymentHandler(
            $this->commandBus->reveal(),
            $this->payum->reveal(),
            $this->tokenStorage->reveal(),
            $this->captureFactory->reveal(),
            $this->getStatusRequestFactory->reveal(),
            $this->resolveNextCommandFactory->reveal(),
        );
    }

    public function test_it_throws_an_exception_if_the_token_is_not_found(): void
    {
        $this->tokenStorage->find('token')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        ($this->handler)(new CapturePaymentCommand('token'));
    }

    public function test_it_executes_capture_flow_and_dispatches_command_retrieved_from_resolve_next_command_action(): void
    {
        $resolveNextCommand = $this->mockCaptureFlow();
        $resolveNextCommand->getNextCommand()->willReturn(new AssertPaymentCommand('next_token'));

        $this->commandBus
            ->dispatch(Argument::type(AssertPaymentCommand::class), [new DispatchAfterCurrentBusStamp()])
            ->willReturn(new Envelope(new \stdClass()))
            ->shouldBeCalled()
        ;

        ($this->handler)(new CapturePaymentCommand('capture_token'));
    }

    public function test_it_executes_capture_flow_and_does_not_dispatch_command_retrieved_from_resolve_next_command_action_if_null(): void
    {
        $resolveNextCommand = $this->mockCaptureFlow();
        $resolveNextCommand->getNextCommand()->willReturn(null);

        $this->commandBus->dispatch(Argument::cetera())->shouldNotBeCalled();

        ($this->handler)(new CapturePaymentCommand('capture_token'));
    }

    /** @return ObjectProphecy<ResolveNextCommandInterface> */
    private function mockCaptureFlow(): ObjectProphecy
    {
        $gateway = $this->prophesize(GatewayInterface::class);
        $captureToken = $this->prophesize(TokenInterface::class);
        $capture = $this->prophesize(Capture::class);
        $getStatus = $this->prophesize(GetStatusInterface::class);
        $resolveNextCommand = $this->prophesize(ResolveNextCommandInterface::class);

        $this->tokenStorage->find('capture_token')->willReturn($captureToken->reveal());

        $captureToken->getGatewayName()->willReturn('saferpay');
        $this->payum->getGateway('saferpay')->willReturn($gateway->reveal());

        $this->captureFactory->createNewWithModel($captureToken->reveal())->willReturn($capture->reveal());
        $gateway->execute($capture->reveal())->shouldBeCalled();

        $capture->getFirstModel()->willReturn($captureToken->reveal());
        $this->getStatusRequestFactory->createNewWithModel($captureToken->reveal())->willReturn($getStatus->reveal());
        $gateway->execute($getStatus->reveal())->shouldBeCalled();

        $this->tokenStorage->delete($captureToken->reveal())->shouldBeCalled();

        $this->resolveNextCommandFactory->createNewWithModel($captureToken->reveal())->willReturn($resolveNextCommand->reveal());
        $gateway->execute($resolveNextCommand->reveal())->shouldBeCalled();

        return $resolveNextCommand;
    }
}
