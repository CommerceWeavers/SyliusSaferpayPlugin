<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Error;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Refund;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert as WebmozartAssert;

final class RefundAction implements ActionInterface
{
    public function __construct(private SaferpayClientInterface $saferpayClient)
    {
    }

    /** @param Refund $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $response = $this->saferpayClient->refund($payment);
        $response = $response;

//        if ($response->getStatusCode() !== Response::HTTP_OK) {
//            $error = $response->getError();
//            WebmozartAssert::notNull($error);
//            $this->handleFailedResponse($payment, $error);
//
//            return;
//        }

//        $this->handleSuccessfulResponse($payment, $response);
    }

    public function supports($request): bool
    {
        return ($request instanceof Refund) && ($request->getModel() instanceof PaymentInterface);
    }

    private function handleFailedResponse(PaymentInterface $payment, Error $response): void
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
        WebmozartAssert::notNull($transaction);
        $paymentDetails['status'] = $transaction->getStatus();
        $paymentDetails['transaction_id'] = $transaction->getId();

        $payment->setDetails($paymentDetails);
    }
}
