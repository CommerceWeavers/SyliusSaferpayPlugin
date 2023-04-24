<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Resolver\SaferpayApiBaseUrlResolverInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Webmozart\Assert\Assert;

final class SaferpayClient implements SaferpayClientInterface
{
    private const PAYMENT_INITIALIZE_URL = 'Payment/v1/PaymentPage/Initialize';

    private const PAYMENT_ASSERT_URL = 'Payment/v1/PaymentPage/Assert';

    private const TRANSACTION_CAPTURE_URL = 'Payment/v1/Transaction/Capture';

    public function __construct(
        private ClientInterface $client,
        private SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        private SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
    ) {
    }

    public function authorize(PaymentInterface $payment, TokenInterface $token): AuthorizeResponse
    {
        $result = $this->request(
            'POST',
            self::PAYMENT_INITIALIZE_URL,
            $this->saferpayClientBodyFactory->createForAuthorize($payment, $token),
            $this->provideGatewayConfig($payment),
        );

        return AuthorizeResponse::fromArray($result);
    }

    public function assert(PaymentInterface $payment): AssertResponse
    {
        $result = $this->request(
            'POST',
            self::PAYMENT_ASSERT_URL,
            $this->saferpayClientBodyFactory->createForAssert($payment),
            $this->provideGatewayConfig($payment),
        );

        return AssertResponse::fromArray($result);
    }

    public function capture(PaymentInterface $payment): CaptureResponse
    {
        $result = $this->request(
            'POST',
            self::TRANSACTION_CAPTURE_URL,
            $this->saferpayClientBodyFactory->createForCapture($payment),
            $this->provideGatewayConfig($payment),
        );

        return CaptureResponse::fromArray($result);
    }

    private function request(string $method, string $url, array $body, GatewayConfigInterface $gatewayConfig): array
    {
        try {
            $response = $this->client->request($method, $this->provideFullUrl($gatewayConfig, $url), [
                'headers' => $this->provideHeaders($gatewayConfig),
                'body' => json_encode($body),
            ]);
        } catch (RequestException $requestException) {
            $response = $requestException->getResponse();
        }

        Assert::notNull($response);

        $responseBody = (array) json_decode($response->getBody()->getContents(), true);
        $responseBody['StatusCode'] = $response->getStatusCode();

        return $responseBody;
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

    private function provideGatewayConfig(PaymentInterface $payment): GatewayConfigInterface
    {
        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $payment->getMethod();
        Assert::notNull($paymentMethod);
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);

        return $gatewayConfig;
    }
}
