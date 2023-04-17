<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Provider\UuidProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Resolver\SaferpayApiBaseUrlResolverInterface;
use GuzzleHttp\ClientInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class SaferpayClient implements SaferpayClientInterface
{
    private const PAYMENT_INITIALIZE_URL = 'Payment/v1/PaymentPage/Initialize';

    private const TRANSACTION_CAPTURE_URL = 'Payment/v1/Transaction/Capture';

    private const SPEC_VERSION = '1.33';

    public function __construct(
        private ClientInterface $client,
        private UuidProviderInterface $uuidProvider,
        private SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
    ) {
    }

    public function authorize(PaymentInterface $payment, TokenInterface $token): array
    {
        $paymentMethod = $payment->getMethod();
        Assert::notNull($paymentMethod);
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);
        $terminalId = (string) $gatewayConfig->getConfig()['terminal_id'];

        $order = $payment->getOrder();
        Assert::notNull($order);
        /** @var string $orderNumber */
        $orderNumber = $order->getNumber();

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

        return $this->request('POST', self::PAYMENT_INITIALIZE_URL, $body, $gatewayConfig);
    }

    public function capture(PaymentInterface $payment): array
    {
        $paymentMethod = $payment->getMethod();
        Assert::notNull($paymentMethod);
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);

        $body = array_merge($this->provideBodyRequestHeader($gatewayConfig), [
            'TransactionReference' => [
                'TransactionId' => $payment->getDetails()['transaction_id'],
            ],
        ]);

        return $this->request('POST', self::TRANSACTION_CAPTURE_URL, $body, $gatewayConfig);
    }

    private function request(string $method, string $url, array $body, GatewayConfigInterface $gatewayConfig): array
    {
        $response = $this->client->request($method, $this->provideFullUrl($gatewayConfig, $url), [
            'headers' => $this->provideHeaders($gatewayConfig),
            'body' => json_encode($body),
        ]);

        return (array) json_decode($response->getBody()->getContents(), true);
    }

    private function provideFullUrl(GatewayConfigInterface $gatewayConfig, string $url): string
    {
        return $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig) . $url;
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
        $customerId = (string) $gatewayConfig->getConfig()['customer_id'];

        return [
            'RequestHeader' => [
                'SpecVersion' => self::SPEC_VERSION,
                'CustomerId' => $customerId,
                'RequestId' => $this->uuidProvider->provide(),
                'RetryIndicator' => 0,
            ]
        ];
    }
}
