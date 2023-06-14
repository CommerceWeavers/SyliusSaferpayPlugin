<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\PaymentRefundFailedException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\RefundInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

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
        if ($response instanceof ErrorResponse) {
            throw new PaymentRefundFailedException();
        }

        $this->handleRefundResponse($payment, $response);

        $transaction = $response->getTransaction();
        Assert::notNull($transaction);
        if ($transaction->getStatus() === StatusAction::STATUS_CAPTURED) {
            return;
        }

        $response = $this->saferpayClient->capture($payment);
        if (!$response->isSuccessful()) {
            throw new PaymentRefundFailedException();
        }

        $this->handleCaptureResponse($payment, $response);
    }

    public function supports($request): bool
    {
        return ($request instanceof RefundInterface) && ($request->getModel() instanceof PaymentInterface);
    }

    private function handleRefundResponse(PaymentInterface $payment, RefundResponse $response): void
    {
        $transaction = $response->getTransaction();
        Assert::notNull($transaction);

        $paymentDetails = $payment->getDetails();
        if ($transaction->getStatus() === StatusAction::STATUS_CAPTURED) {
            $paymentDetails['status'] = StatusAction::STATUS_REFUNDED;
            $paymentDetails['capture_id'] = $transaction->getCaptureId();
        } else {
            $paymentDetails['transaction_id'] = $transaction->getId();
        }

        $payment->setDetails($paymentDetails);
    }

    private function handleCaptureResponse(PaymentInterface $payment, CaptureResponse $response): void
    {
        $paymentDetails = $payment->getDetails();

        $paymentDetails['status'] = StatusAction::STATUS_REFUNDED;
        $paymentDetails['capture_id'] = $response->getCaptureId();

        $payment->setDetails($paymentDetails);
    }
}
