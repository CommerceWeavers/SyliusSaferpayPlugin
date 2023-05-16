<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\Handler;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\AssertPaymentCommand;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\AssertFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\ResolveNextCommandFactoryInterface;
use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Webmozart\Assert\Assert;

final class AssertPaymentHandler
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Payum $payum,
        private StorageInterface $tokenStorage,
        private AssertFactoryInterface $assertFactory,
        private GetStatusFactoryInterface $getStatusRequestFactory,
        private ResolveNextCommandFactoryInterface $resolveNextCommandFactory,
    ) {
    }

    public function __invoke(AssertPaymentCommand $command): void
    {
        /** @var TokenInterface|null $token */
        $token = $this->tokenStorage->find($command->getPayumToken());
        Assert::notNull($token, 'Token not found.');

        $gateway = $this->payum->getGateway($token->getGatewayName());

        $assert = $this->assertFactory->createNewWithModel($token);
        $gateway->execute($assert);

        /** @var PaymentInterface $assertModel */
        $assertModel = $assert->getFirstModel();

        $status = $this->getStatusRequestFactory->createNewWithModel($assertModel);
        $gateway->execute($status);

        $this->tokenStorage->delete($token);

        $resolvedNextCommand = $this->resolveNextCommandFactory->createNewWithModel($assertModel);
        $gateway->execute($resolvedNextCommand);

        $nextCommand = $resolvedNextCommand->getNextCommand();
        if (null === $nextCommand) {
            return;
        }

        $this->commandBus->dispatch($nextCommand, [new DispatchAfterCurrentBusStamp()]);
    }
}
