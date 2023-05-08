<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\RefundInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\Response;

final class RefundAction implements ActionInterface
{
    public function __construct(private SaferpayClientInterface $saferpayClient)
    {
    }

    /** @param RefundInterface $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $response = $this->saferpayClient->refund($payment);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $payment->setDetails([
                'status' => StatusAction::STATUS_REFUND_FAILED,
            ]);

            return;
        }

        $this->handleRefundResponse($payment, $response);

        $response = $this->saferpayClient->capture($payment);
        $this->handleCaptureResponse($payment, $response);
    }

    public function supports($request): bool
    {
        return ($request instanceof RefundInterface) && ($request->getModel() instanceof PaymentInterface);
    }

    private function handleRefundResponse(PaymentInterface $payment, RefundResponse $response): void
    {
        $transaction = $response->getTransaction();

        $paymentDetails = $payment->getDetails();
        $paymentDetails['status'] = StatusAction::STATUS_REFUND_AUTHORIZED;
        $paymentDetails['transaction_id'] = $transaction->getId();

        $payment->setDetails($paymentDetails);
    }

    private function handleCaptureResponse(PaymentInterface $payment, CaptureResponse $response): void
    {
        $paymentDetails = $payment->getDetails();

        $isSuccessfulResponse = $response->getStatusCode() === Response::HTTP_OK;
        $paymentDetails['status'] = $isSuccessfulResponse ? StatusAction::STATUS_REFUNDED : StatusAction::STATUS_REFUND_FAILED;
        $paymentDetails['capture_id'] = $response->getCaptureId();

        $payment->setDetails($paymentDetails);
    }
}
