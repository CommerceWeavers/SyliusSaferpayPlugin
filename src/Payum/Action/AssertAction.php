<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Assert;
use GuzzleHttp\ClientInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Ramsey\Uuid\Uuid;
use Sylius\Component\Core\Model\PaymentInterface;

final class AssertAction implements ActionInterface
{
    public function __construct(
        private ClientInterface $httpClient,
    ) {
    }

    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $body = [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => '268229',
                'RequestId' => Uuid::uuid4()->toString(),
                'RetryIndicator' => 0,
            ],
            'Token' => $payment->getDetails()['saferpay_token'],
        ];

        $response = $this->httpClient->request('POST', 'https://test.saferpay.com/api/Payment/v1/PaymentPage/Assert', [
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

        $paymentDetails['status'] = $decodedResponse['Transaction']['Status'];
        $paymentDetails['transaction_id'] = $decodedResponse['Transaction']['Id'];

        $payment->setDetails($paymentDetails);
    }

    public function supports($request): bool
    {
        return ($request instanceof Assert) && ($request->getModel() instanceof PaymentInterface);
    }
}
