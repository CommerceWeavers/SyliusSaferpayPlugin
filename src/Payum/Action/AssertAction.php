<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Assert\FailedResponseHandlerInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Assert\SuccessfulResponseHandlerInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Assert;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Core\Model\PaymentInterface;

final class AssertAction implements ActionInterface
{
    public function __construct(
        private SaferpayClientInterface $saferpayClient,
        private SuccessfulResponseHandlerInterface $successfulResponseHandler,
        private FailedResponseHandlerInterface $failedResponseHandler,
    ) {
    }

    /** @param Assert $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        /** @var AssertResponse|ErrorResponse $response */
        $response = $this->saferpayClient->assert($payment);

        if ($response instanceof ErrorResponse) {
            $this->failedResponseHandler->handle($payment, $response);

            return;
        }

        $this->successfulResponseHandler->handle($payment, $response);
    }

    public function supports($request): bool
    {
        return ($request instanceof Assert) && ($request->getModel() instanceof PaymentInterface);
    }
}
