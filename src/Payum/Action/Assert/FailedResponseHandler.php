<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Assert;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\ErrorName;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use Sylius\Component\Core\Model\PaymentInterface;

final class FailedResponseHandler implements FailedResponseHandlerInterface
{
    public function handle(PaymentInterface $payment, ErrorResponse $response): void
    {
        $paymentDetails = $payment->getDetails();

        $paymentDetails['transaction_id'] = $response->getTransactionId();
        $paymentDetails['status'] = $response->getName() === ErrorName::TRANSACTION_ABORTED
            ? StatusAction::STATUS_CANCELLED
            : StatusAction::STATUS_FAILED;

        $payment->setDetails($paymentDetails);
    }
}
