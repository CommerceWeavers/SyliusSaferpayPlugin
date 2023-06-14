<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ResponseInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\EventDispatcher\PaymentEventDispatcherInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Resolver\SaferpayApiBaseUrlResolverInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Webmozart\Assert\Assert;

final class SaferpayClient implements SaferpayClientInterface
{
    private const PAYMENT_ASSERT_URL = 'Payment/v1/PaymentPage/Assert';

    private const PAYMENT_INITIALIZE_URL = 'Payment/v1/PaymentPage/Initialize';

    private const TRANSACTION_CAPTURE_URL = 'Payment/v1/Transaction/Capture';

    private const TRANSACTION_REFUND_URL = 'Payment/v1/Transaction/Refund';

    public function __construct(
        private HttpClientInterface $client,
        private SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        private SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        private PaymentEventDispatcherInterface $paymentEventDispatcher,
    ) {
    }

    public function authorize(PaymentInterface $payment, TokenInterface $token): ResponseInterface
    {
        $payload = $this->saferpayClientBodyFactory->createForAuthorize($payment, $token);
        $result = $this->request(
            'POST',
            self::PAYMENT_INITIALIZE_URL,
            $payload,
            $this->provideGatewayConfig($payment),
        );


        if (200 === $result['StatusCode']) {
            $response = AuthorizeResponse::fromArray($result);

            $this->paymentEventDispatcher->dispatchAuthorizationSucceededEvent(
                $payment,
                self::PAYMENT_INITIALIZE_URL,
                $payload,
                $response,
            );
        } else {
            $response = ErrorResponse::forAuthorize($result);

            $this->paymentEventDispatcher->dispatchAuthorizationFailedEvent(
                $payment,
                self::PAYMENT_INITIALIZE_URL,
                $payload,
                $response,
            );
        }

        return $response;
    }

    public function assert(PaymentInterface $payment): ResponseInterface
    {
        $payload = $this->saferpayClientBodyFactory->createForAssert($payment);
        $result = $this->request(
            'POST',
            self::PAYMENT_ASSERT_URL,
            $payload,
            $this->provideGatewayConfig($payment),
        );

        if (200 === $result['StatusCode']) {
            $response = AssertResponse::fromArray($result);

            $this->paymentEventDispatcher->dispatchAssertionSucceededEvent(
                $payment,
                self::PAYMENT_ASSERT_URL,
                $payload,
                $response,
            );
        } else {
            $response = ErrorResponse::forAssert($result);

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

        if ($response->isSuccessful()) {
            $this->paymentEventDispatcher->dispatchCaptureSucceededEvent(
                $payment,
                self::TRANSACTION_CAPTURE_URL,
                $payload,
                $response,
            );
        } else {
            $this->paymentEventDispatcher->dispatchCaptureFailedEvent(
                $payment,
                self::TRANSACTION_CAPTURE_URL,
                $payload,
                $response,
            );
        }

        return $response;
    }

    public function refund(PaymentInterface $payment): ResponseInterface
    {
        $payload = $this->saferpayClientBodyFactory->createForRefund($payment);
        $result = $this->request(
            'POST',
            self::TRANSACTION_REFUND_URL,
            $payload,
            $this->provideGatewayConfig($payment),
        );

        if (200 === $result['StatusCode']) {
            $response = RefundResponse::fromArray($result);

            $this->paymentEventDispatcher->dispatchRefundSucceededEvent(
                $payment,
                self::TRANSACTION_REFUND_URL,
                $payload,
                $response,
            );
        } else {
            $response = ErrorResponse::forRefund($result);

            $this->paymentEventDispatcher->dispatchRefundFailedEvent(
                $payment,
                self::TRANSACTION_REFUND_URL,
                $payload,
                $response,
            );
        }

        return $response;
    }

    public function getTerminal(GatewayConfigInterface $gatewayConfig): array
    {
        $customerId = (string) $gatewayConfig->getConfig()['customer_id'];
        $terminalId = (string) $gatewayConfig->getConfig()['terminal_id'];

        $result = $this->request(
            'GET',
            sprintf('rest/customers/%s/terminals/%s', $customerId, $terminalId),
            [],
            $gatewayConfig,
            $this->saferpayClientBodyFactory->provideHeadersForTerminal(),
        );

        return $result;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    private function request(
        string $method,
        string $url,
        array $body,
        GatewayConfigInterface $gatewayConfig,
        array $headers = [],
    ): array {
        $response = $this->client->request($method, $this->provideFullUrl($gatewayConfig, $url), [
            'headers' => array_merge($this->provideHeaders($gatewayConfig), $headers),
            'body' => json_encode($body),
        ]);

        $responseBody = (array) json_decode($response->getContent(false), true);
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
