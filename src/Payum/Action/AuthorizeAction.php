<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Ramsey\Uuid\Uuid;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class AuthorizeAction implements ActionInterface
{
    public function __construct(
        private ClientInterface $httpClient,
    ) {
    }

    /**
     * @param Capture $request
     *
     * @throws GuzzleException
     */
    public function execute(mixed $request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        $token = $request->getToken();
        Assert::notNull($token);

        $body = [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => '268229',
                'RequestId' => Uuid::uuid4()->toString(),
                'RetryIndicator' => 0,
            ],
            'TerminalId' => '17757531',
            'Payment' => [
                'Amount' => [
                    'Value' => $payment->getAmount(),
                    'CurrencyCode' => $payment->getCurrencyCode(),
                ],
                'OrderId' => $payment->getOrder()->getNumber(),
                'Description' => sprintf('Payment for order %s', $payment->getOrder()->getNumber()),
            ],
            'ReturnUrl' => [
                'Url' => $token->getAfterUrl(),
            ],
        ];

        $response = $this->httpClient->request('POST', 'https://test.saferpay.com/api/Payment/v1/PaymentPage/Initialize', [
            'body' => json_encode($body),
            'headers' => [
                'Authorization' => 'Basic QVBJXzI2ODIyOV8yNDQyMDU5OTpKc29uQXBpUHdkMV82RjRjU2IsKGk9KVc=',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        /** @var array{RedirectUrl: string} $decodedResponse */
        $decodedResponse = json_decode($response->getBody()->getContents(), true);

        $token->setAfterUrl($decodedResponse['RedirectUrl']);
        $payment->setDetails([
            'request_id' => $decodedResponse['ResponseHeader']['RequestId'],
            'saferpay_token' => $decodedResponse['Token'],
            'status' => StatusAction::STATUS_NEW,
        ]);
    }

    public function supports($request): bool
    {
        return ($request instanceof Authorize) && ($request->getModel() instanceof PaymentInterface);
    }
}
