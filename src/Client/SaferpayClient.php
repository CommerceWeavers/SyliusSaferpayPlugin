<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Resolver\SaferpayApiBaseUrlResolverInterface;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Event\PaymentAssertionFailed;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Event\PaymentAssertionSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Event\PaymentAuthorizationSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Event\PaymentCaptureSucceeded;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Messenger\MessageBusInterface;
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
        private MessageBusInterface $eventBus,
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

        $this->dispatchPaymentAuthorizationSucceededEvent($payload, $response);

        return $response;
    }

    private function dispatchPaymentAuthorizationSucceededEvent(array $request, AuthorizeResponse $response): void
    {
        $this->eventBus->dispatch(
            new PaymentAuthorizationSucceeded(self::PAYMENT_INITIALIZE_URL, $request, $response->toArray()),
        );
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
            $this->dispatchPaymentAssertionSucceededEvent($payload, $response);
        } else {
            $this->dispatchPaymentAssertionFailedEvent($payload, $response);
        }

        return $response;
    }

    private function dispatchPaymentAssertionSucceededEvent(array $request, AssertResponse $response): void
    {
        $this->eventBus->dispatch(
            new PaymentAssertionSucceeded(self::PAYMENT_ASSERT_URL, $request, $response->toArray()),
        );
    }

    private function dispatchPaymentAssertionFailedEvent(array $request, AssertResponse $response): void
    {
        $this->eventBus->dispatch(
            new PaymentAssertionFailed(self::PAYMENT_ASSERT_URL, $request, $response->toArray()),
        );
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

        $this->dispatchPaymentCaptureSucceededEvent($payload, $response);

        return $response;
    }

    private function dispatchPaymentCaptureSucceededEvent(array $request, CaptureResponse $response): void
    {
        $this->eventBus->dispatch(
            new PaymentCaptureSucceeded(self::TRANSACTION_CAPTURE_URL, $request, $response->toArray()),
        );
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
