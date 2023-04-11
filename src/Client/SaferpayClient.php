<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Provider\UuidProviderInterface;
use GuzzleHttp\ClientInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Webmozart\Assert\Assert;

final class SaferpayClient implements SaferpayClientInterface
{
    public function __construct(
        private ClientInterface $client,
        private UuidProviderInterface $uuidProvider,
        private string $baseUrl
    ) {
    }

    public function authorize(PaymentInterface $payment, TokenInterface $token): array
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();
        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        $terminalId = (string) $gatewayConfig->getConfig()['terminalId'];

        /** @var OrderInterface $order */
        $order = $payment->getOrder();
        $orderNumber = $order->getNumber();
        Assert::string($orderNumber);

        $body = array_merge($this->provideBodyRequestHeader($gatewayConfig), [
            'TerminalId' => $terminalId,
            'Payment' => [
                'Amount' => [
                    'Value' => $payment->getAmount(),
                    'CurrencyCode' => $payment->getCurrencyCode(),
                ],
                'OrderId' => $orderNumber,
                'Description' => sprintf('Payment for order %s', $orderNumber),
            ],
            'ReturnUrl' => [
                'Url' => $token->getAfterUrl(),
            ],
        ]);

        return $this->request('POST', 'PaymentPage/Initialize', $body, $gatewayConfig);
    }

    public function capture(PaymentInterface $payment): array
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();
        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        $body = array_merge($this->provideBodyRequestHeader($gatewayConfig), [
            'TransactionReference' => [
                'TransactionId' => $payment->getDetails()['transaction_id'],
            ],
        ]);

        return $this->request('POST', 'Transaction/Capture', $body, $gatewayConfig);
    }

    private function request(string $method, string $url, array $body, GatewayConfigInterface $gatewayConfig): array
    {
        $response = $this->client->request($method, $this->provideFullUrl($url), [
            'headers' => $this->provideHeaders($gatewayConfig),
            'body' => json_encode($body),
        ]);

        return (array) json_decode($response->getBody()->getContents(), true);
    }

    private function provideFullUrl(string $url): string
    {
        return $this->baseUrl . $url;
    }

    private function provideHeaders(GatewayConfigInterface $gatewayConfig): array
    {
        $username = (string) $gatewayConfig->getConfig()['username'];
        $password = (string) $gatewayConfig->getConfig()['password'];

        return [
            'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    private function provideBodyRequestHeader(GatewayConfigInterface $gatewayConfig): array
    {
        $customerId = (string) $gatewayConfig->getConfig()['customerId'];

        return [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => $customerId,
                'RequestId' => $this->uuidProvider->provide(),
                'RetryIndicator' => 0,
            ]
        ];
    }
}
