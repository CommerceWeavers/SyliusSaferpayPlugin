<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Assert;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use Sylius\Component\Core\Model\PaymentInterface;

final class SuccessfulResponseHandler implements SuccessfulResponseHandlerInterface
{
    public function handle(PaymentInterface $payment, AssertResponse $response): void
    {
        $paymentDetails = $payment->getDetails();

        $transaction = $response->getTransaction();
        $paymentDetails['status'] = $transaction->getStatus();
        $paymentDetails['transaction_id'] = $transaction->getId();

        $payment->setDetails($paymentDetails);
    }
}
