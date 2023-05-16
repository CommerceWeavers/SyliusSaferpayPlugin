<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\AssertPaymentCommand;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\CapturePaymentCommand;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\ResolveNextCommandInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusCheckerInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Core\Model\PaymentInterface;

final class ResolveNextCommandAction implements ActionInterface
{
    public function __construct(
        private TokenProviderInterface $tokenProvider,
        private StatusCheckerInterface $statusChecker,
    ) {
    }

    /** @param ResolveNextCommandInterface $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $token = $this->tokenProvider->provideForCommandHandler($request->getFirstModel());

        if ($this->statusChecker->isNew($payment)) {
            $request->setNextCommand(new AssertPaymentCommand($token->getHash()));
            return;
        }

        if ($this->statusChecker->isAuthorized($payment)) {
            $request->setNextCommand(new CapturePaymentCommand($token->getHash()));
            return;
        }

        $request->setNextCommand(null);
    }

    public function supports($request): bool
    {
        return $request instanceof ResolveNextCommandInterface && $request->getModel() instanceof PaymentInterface;
    }
}
