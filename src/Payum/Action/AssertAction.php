<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Assert;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Core\Model\PaymentInterface;

final class AssertAction implements ActionInterface
{
    public function __construct(private SaferpayClientInterface $saferpayClient)
    {
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
            $this->handleFailedResponse($payment, $response);

            return;
        }

        $this->handleSuccessfulResponse($payment, $response);
    }

    public function supports($request): bool
    {
        return ($request instanceof Assert) && ($request->getModel() instanceof PaymentInterface);
    }

    private function handleFailedResponse(PaymentInterface $payment, ErrorResponse $response): void
    {
        $paymentDetails = $payment->getDetails();
        $paymentDetails['transaction_id'] = $response->getTransactionId();

        if ($response->getName() === ErrorName::TRANSACTION_ABORTED) {
            $paymentDetails['status'] = StatusAction::STATUS_CANCELLED;
            $payment->setDetails($paymentDetails);

            return;
        }

        $paymentDetails['status'] = StatusAction::STATUS_FAILED;

        $payment->setDetails($paymentDetails);
    }

    private function handleSuccessfulResponse(PaymentInterface $payment, AssertResponse $response): void
    {
        $paymentDetails = $payment->getDetails();

        $transaction = $response->getTransaction();
        $paymentDetails['status'] = $transaction->getStatus();
        $paymentDetails['transaction_id'] = $transaction->getId();

        $payment->setDetails($paymentDetails);
    }
}
