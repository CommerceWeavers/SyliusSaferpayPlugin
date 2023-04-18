<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusCheckerInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Sylius\Component\Core\Model\PaymentInterface;

final class CaptureAction implements ActionInterface
{
    public function __construct(
        private SaferpayClientInterface $saferpayClient,
        private StatusCheckerInterface $statusChecker,
    ) {
    }

    /** @param Capture $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        if ($this->statusChecker->isCaptured($payment)) {
            return;
        }

        $response = $this->saferpayClient->capture($payment);

        $paymentDetails = $payment->getDetails();
        $paymentDetails['status'] = $response->getStatus();

        $payment->setDetails($paymentDetails);
    }

    public function supports($request): bool
    {
        return $request instanceof Capture && $request->getFirstModel() instanceof PaymentInterface;
    }
}
