<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\Handler;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\CapturePaymentCommand;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\CaptureFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\ResolveNextCommandFactoryInterface;
use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Webmozart\Assert\Assert;

final class CapturePaymentHandler
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Payum $payum,
        private StorageInterface $tokenStorage,
        private CaptureFactoryInterface $captureFactory,
        private GetStatusFactoryInterface $getStatusRequestFactory,
        private ResolveNextCommandFactoryInterface $resolveNextCommandFactory,
    ) {
    }

    public function __invoke(CapturePaymentCommand $command): void
    {
        /** @var TokenInterface|null $token */
        $token = $this->tokenStorage->find($command->getPayumToken());
        Assert::notNull($token, 'Token not found.');

        $gateway = $this->payum->getGateway($token->getGatewayName());

        $capture = $this->captureFactory->createNewWithModel($token);
        $gateway->execute($capture);

        /** @var PaymentInterface $payment */
        $payment = $capture->getFirstModel();

        $status = $this->getStatusRequestFactory->createNewWithModel($payment);
        $gateway->execute($status);

        $this->tokenStorage->delete($token);

        $resolvedNextCommand = $this->resolveNextCommandFactory->createNewWithModel($payment);
        $gateway->execute($resolvedNextCommand);

        $nextCommand = $resolvedNextCommand->getNextCommand();
        if (null === $nextCommand) {
            return;
        }

        $this->commandBus->dispatch($nextCommand, [new DispatchAfterCurrentBusStamp()]);
    }
}
