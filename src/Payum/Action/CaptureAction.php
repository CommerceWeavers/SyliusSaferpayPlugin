<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use GuzzleHttp\ClientInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Ramsey\Uuid\Uuid;
use Sylius\Component\Core\Model\PaymentInterface;

final class CaptureAction implements ActionInterface
{
    public function __construct(
        private ClientInterface $httpClient,
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

        $body = [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => '268229',
                'RequestId' => Uuid::uuid4()->toString(),
                'RetryIndicator' => 0,
            ],
            'TransactionReference' => [
                'TransactionId' => $payment->getDetails()['transaction_id'],
            ],
        ];

        $response = $this->httpClient->request('POST', 'https://test.saferpay.com/api/Payment/v1/Transaction/Capture', [
            'body' => json_encode($body),
            'headers' => [
                'Authorization' => 'Basic QVBJXzI2ODIyOV8yNDQyMDU5OTpKc29uQXBpUHdkMV82RjRjU2IsKGk9KVc=',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        /** @var array $decodedResponse */
        $decodedResponse = json_decode($response->getBody()->getContents(), true);

        $paymentDetails = $payment->getDetails();

        $paymentDetails['status'] = $decodedResponse['Status'];

        $payment->setDetails($paymentDetails);
    }

    public function supports($request): bool
    {
        return $request instanceof Capture && $request->getFirstModel() instanceof PaymentInterface;
    }
}
