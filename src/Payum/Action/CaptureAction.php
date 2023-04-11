<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Sylius\Component\Core\Model\PaymentInterface;

final class CaptureAction implements ActionInterface
{
    public function __construct (
        private SaferpayClientInterface $saferpayClient,
    ) {
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        if (StatusAction::STATUS_CAPTURED === $payment->getDetails()['status']) {
            return;
        }

        $response = $this->saferpayClient->capture($payment);

        $paymentDetails = $payment->getDetails();
        $paymentDetails['status'] = $response['Status'];

        $payment->setDetails($paymentDetails);
    }

    public function supports($request): bool
    {
        return $request instanceof Capture && $request->getFirstModel() instanceof PaymentInterface;
    }
}
