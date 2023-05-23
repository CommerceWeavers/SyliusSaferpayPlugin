<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\EventDispatcher\PaymentEventDispatcherInterface;
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

    private const TRANSACTION_REFUND_URL = 'Payment/v1/Transaction/Refund';

    public function __construct(
        private ClientInterface $client,
        private SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        private SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        private PaymentEventDispatcherInterface $paymentEventDispatcher,
    ) {
    }

    public function authorize(PaymentInterface $payment, TokenInterface $token): AuthorizeResponse
    {
        $payload = $this->saferpayClientBodyFactory->createForAuthorize($payment, $token);
        $result = $this->request(
            'POST',
            self::PAYMENT_INITIALIZE_URL,
            $payload,
            $this->provideGatewayConfig($payment),
        );

        $response = AuthorizeResponse::fromArray($result);

        $this->paymentEventDispatcher->dispatchAuthorizationSucceededEvent(
            $payment,
            self::PAYMENT_INITIALIZE_URL,
            $payload,
            $response,
        );

        return $response;
    }

    public function assert(PaymentInterface $payment): AssertResponse
    {
        $payload = $this->saferpayClientBodyFactory->createForAssert($payment);
        $result = $this->request(
            'POST',
            self::PAYMENT_ASSERT_URL,
            $payload,
            $this->provideGatewayConfig($payment),
        );

        $response = AssertResponse::fromArray($result);

        if ($response->isSuccessful()) {
            $this->paymentEventDispatcher->dispatchAssertionSucceededEvent(
                $payment,
                self::PAYMENT_ASSERT_URL,
                $payload,
                $response,
            );
        } else {
            $this->paymentEventDispatcher->dispatchAssertionFailedEvent(
                $payment,
                self::PAYMENT_ASSERT_URL,
                $payload,
                $response,
            );
        }

        return $response;
    }

    public function capture(PaymentInterface $payment): CaptureResponse
    {
        $payload = $this->saferpayClientBodyFactory->createForCapture($payment);
        $result = $this->request(
            'POST',
            self::TRANSACTION_CAPTURE_URL,
            $payload,
            $this->provideGatewayConfig($payment),
        );

        $response = CaptureResponse::fromArray($result);

        $this->paymentEventDispatcher->dispatchCaptureSucceededEvent(
            $payment,
            self::TRANSACTION_CAPTURE_URL,
            $payload,
            $response,
        );

        return $response;
    }

    public function refund(PaymentInterface $payment): RefundResponse
    {
        $payload = $this->saferpayClientBodyFactory->createForRefund($payment);
        $result = $this->request(
            'POST',
            self::TRANSACTION_REFUND_URL,
            $payload,
            $this->provideGatewayConfig($payment),
        );

        $response = RefundResponse::fromArray($result);

        if ($response->isSuccessful()) {
            $this->paymentEventDispatcher->dispatchRefundSucceededEvent(
                $payment,
                self::TRANSACTION_REFUND_URL,
                $payload,
                $response,
            );
        } else {
            $this->paymentEventDispatcher->dispatchPaymentRefundFailedEvent(
                $payment,
                self::TRANSACTION_REFUND_URL,
                $payload,
                $response,
            );
        }

        return $response;
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
