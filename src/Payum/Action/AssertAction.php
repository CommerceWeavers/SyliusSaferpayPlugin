<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Assert;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert as WebmozartAssert;

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

        $response = $this->saferpayClient->assert($payment);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $this->handleFailedResponse($payment, $response);

            return;
        }

        $this->handleSuccessfulResponse($payment, $response);
    }

    public function supports($request): bool
    {
        return ($request instanceof Assert) && ($request->getModel() instanceof PaymentInterface);
    }

    private function handleFailedResponse(PaymentInterface $payment, AssertResponse $response): void
    {
        $paymentDetails = $payment->getDetails();
        $paymentDetails['status'] = StatusAction::STATUS_FAILED;

        $error = $response->getError();
        WebmozartAssert::notNull($error);
        $paymentDetails['transaction_id'] = $error->getTransactionId();

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
