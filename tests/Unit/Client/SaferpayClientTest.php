<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClient;
use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientBodyFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\EventDispatcher\PaymentEventDispatcherInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Resolver\SaferpayApiBaseUrlResolverInterface;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\HttpClient\Exception\TimeoutException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class SaferpayClientTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $client;

    private ObjectProphecy $saferpayClientBodyFactory;

    private ObjectProphecy $saferpayApiBaseUrlResolver;

    private ObjectProphecy $paymentEventDispatcher;

    private ObjectProphecy $logger;

    private SaferpayClient $saferpayClient;

    protected function setUp(): void
    {
        $this->client = $this->prophesize(HttpClientInterface::class);
        $this->saferpayClientBodyFactory = $this->prophesize(SaferpayClientBodyFactoryInterface::class);
        $this->saferpayApiBaseUrlResolver = $this->prophesize(SaferpayApiBaseUrlResolverInterface::class);
        $this->paymentEventDispatcher = $this->prophesize(PaymentEventDispatcherInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->saferpayClient = new SaferpayClient(
            $this->client->reveal(),
            $this->saferpayClientBodyFactory->reveal(),
            $this->saferpayApiBaseUrlResolver->reveal(),
            $this->paymentEventDispatcher->reveal(),
            $this->logger->reveal(),
        );
    }

    public function test_it_implements_saferpay_client_interface(): void
    {
        self::assertInstanceOf(SaferpayClientInterface::class, $this->saferpayClient);
    }

    public function test_it_performs_authorize_request(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $response = $this->prophesize(ResponseInterface::class);

        $payload = $this->getExampleAuthorizePayload();
        $this->saferpayClientBodyFactory->createForAuthorize($payment, $token)->willReturn($payload);
        $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $this->client
            ->request(
                'POST',
                'https://test.saferpay.com/api/Payment/v1/PaymentPage/Initialize',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode('USERNAME:PASSWORD'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($payload),
                ],
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(200);
        $response->getHeaders(false)->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleAuthorizeResponse());

        $this->paymentEventDispatcher
            ->dispatchAuthorizationSucceededEvent(
                $payment,
                'Payment/v1/PaymentPage/Initialize',
                $payload,
                AuthorizeResponse::fromArray(array_merge(['StatusCode' => 200], json_decode($this->getExampleAuthorizeResponse(), true))),
            )
            ->shouldBeCalled()
        ;

        self::assertInstanceOf(AuthorizeResponse::class, $this->saferpayClient->authorize($payment->reveal(), $token->reveal()));
    }

    public function test_it_dispatches_a_failed_event_once_the_authorization_fails(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $response = $this->prophesize(ResponseInterface::class);

        $payload = $this->getExampleAuthorizePayload();
        $this->saferpayClientBodyFactory->createForAuthorize($payment, $token)->willReturn($payload);
        $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $token->getAfterUrl()->willReturn('https://example.com/after');

        $this->client
            ->request(
                'POST',
                'https://test.saferpay.com/api/Payment/v1/PaymentPage/Initialize',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode('USERNAME:PASSWORD'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($payload),
                ],
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(402);
        $response->getHeaders(false)->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleAuthorizeErrorResponse());

        $this->paymentEventDispatcher
            ->dispatchAuthorizationFailedEvent(
                $payment,
                'Payment/v1/PaymentPage/Initialize',
                $payload,
                ErrorResponse::forAuthorize(array_merge(['StatusCode' => 402], json_decode($this->getExampleAuthorizeErrorResponse(), true))),
            )
            ->shouldBeCalled()
        ;

        self::assertInstanceOf(ErrorResponse::class, $this->saferpayClient->authorize($payment->reveal(), $token->reveal()));
    }

    public function test_it_handles_failed_authorize_request_with_content_different_than_json(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $response = $this->prophesize(ResponseInterface::class);

        $payload = $this->getExampleAuthorizePayload();
        $this->saferpayClientBodyFactory->createForAuthorize($payment, $token)->willReturn($payload);
        $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $token->getAfterUrl()->willReturn('https://example.com/after');

        $this->client
            ->request(
                'POST',
                'https://test.saferpay.com/api/Payment/v1/PaymentPage/Initialize',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode('USERNAME:PASSWORD'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($payload),
                ],
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(402);
        $response->getHeaders(false)->willReturn(['content-type' => ['text/html']]);
        $response->getContent(false)->willReturn('Non JSON response content');

        $this->paymentEventDispatcher
            ->dispatchAuthorizationFailedEvent(
                $payment,
                'Payment/v1/PaymentPage/Initialize',
                $payload,
                ErrorResponse::generalError('Non JSON response content', 'Authorize'),
            )
            ->shouldBeCalled()
        ;

        self::assertInstanceOf(ErrorResponse::class, $this->saferpayClient->authorize($payment->reveal(), $token->reveal()));
    }

    public function test_it_performs_assert_request(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $response = $this->prophesize(ResponseInterface::class);

        $payload = [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Token' => 'TOKEN',
        ];
        $this->saferpayClientBodyFactory->createForAssert($payment)->willReturn($payload);
        $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['saferpay_token' => 'TOKEN']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $this->client
            ->request(
                'POST',
                'https://test.saferpay.com/api/Payment/v1/PaymentPage/Assert',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode('USERNAME:PASSWORD'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($payload),
                ],
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(200);
        $response->getHeaders(false)->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleAssertResponse());

        $this->paymentEventDispatcher
            ->dispatchAssertionSucceededEvent(
                $payment,
                'Payment/v1/PaymentPage/Assert',
                $payload,
                AssertResponse::fromArray(array_merge(['StatusCode' => 200], json_decode($this->getExampleAssertResponse(), true))),
            )
            ->shouldBeCalled()
        ;

        self::assertInstanceOf(AssertResponse::class, $this->saferpayClient->assert($payment->reveal()));
    }

    public function test_it_performs_failed_assert_request(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $response = $this->prophesize(ResponseInterface::class);

        $payload = [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Token' => 'TOKEN',
        ];
        $this->saferpayClientBodyFactory->createForAssert($payment)->willReturn($payload);
        $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['saferpay_token' => 'TOKEN']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $this->client
            ->request(
                'POST',
                'https://test.saferpay.com/api/Payment/v1/PaymentPage/Assert',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode('USERNAME:PASSWORD'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($payload),
                ],
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(400);
        $response->getHeaders(false)->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleAssertFailedResponse());

        $this->paymentEventDispatcher
            ->dispatchAssertionFailedEvent(
                $payment,
                'Payment/v1/PaymentPage/Assert',
                $payload,
                ErrorResponse::forAssert(array_merge(['StatusCode' => 400], json_decode($this->getExampleAssertFailedResponse(), true))),
            )
            ->shouldBeCalled()
        ;

        self::assertInstanceOf(ErrorResponse::class, $this->saferpayClient->assert($payment->reveal()));
    }

    public function test_it_handles_failed_assert_request_with_content_different_than_json(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $response = $this->prophesize(ResponseInterface::class);

        $payload = [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Token' => 'TOKEN',
        ];
        $this->saferpayClientBodyFactory->createForAssert($payment)->willReturn($payload);
        $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['saferpay_token' => 'TOKEN']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $this->client
            ->request(
                'POST',
                'https://test.saferpay.com/api/Payment/v1/PaymentPage/Assert',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode('USERNAME:PASSWORD'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($payload),
                ],
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(400);
        $response->getHeaders(false)->willReturn(['content-type' => ['text/html']]);
        $response->getContent(false)->willReturn('Non JSON response content');

        $this->paymentEventDispatcher
            ->dispatchAssertionFailedEvent(
                $payment,
                'Payment/v1/PaymentPage/Assert',
                $payload,
                ErrorResponse::generalError('Non JSON response content', 'Assert'),
            )
            ->shouldBeCalled()
        ;

        self::assertInstanceOf(ErrorResponse::class, $this->saferpayClient->assert($payment->reveal()));
    }

    public function test_it_performs_capture_request(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $response = $this->prophesize(ResponseInterface::class);

        $payload = $this->getExampleCapturePayload();
        $this->saferpayClientBodyFactory->createForCapture($payment)->willReturn($payload);
        $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['transaction_id' => '123456789']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $this->client
            ->request(
                'POST',
                'https://test.saferpay.com/api/Payment/v1/Transaction/Capture',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode('USERNAME:PASSWORD'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($payload),
                ],
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(200);
        $response->getHeaders(false)->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleCaptureResponse());

        $this->paymentEventDispatcher
            ->dispatchCaptureSucceededEvent(
                $payment,
                'Payment/v1/Transaction/Capture',
                $payload,
                CaptureResponse::fromArray(array_merge(['StatusCode' => 200], json_decode($this->getExampleCaptureResponse(), true))),
            )
            ->shouldBeCalled()
        ;

        self::assertInstanceOf(CaptureResponse::class, $this->saferpayClient->capture($payment->reveal()));
    }

    public function test_it_dispatches_a_failed_event_once_the_capture_fails(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $response = $this->prophesize(ResponseInterface::class);

        $payload = $this->getExampleCapturePayload();
        $this->saferpayClientBodyFactory->createForCapture($payment)->willReturn($payload);
        $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['transaction_id' => '123456789']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $this->client
            ->request(
                'POST',
                'https://test.saferpay.com/api/Payment/v1/Transaction/Capture',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode('USERNAME:PASSWORD'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($payload),
                ],
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(402);
        $response->getHeaders(false)->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleCaptureErrorResponse());

        $this->paymentEventDispatcher
            ->dispatchCaptureFailedEvent(
                $payment,
                'Payment/v1/Transaction/Capture',
                $payload,
                ErrorResponse::forCapture(array_merge(['StatusCode' => 402], json_decode($this->getExampleCaptureErrorResponse(), true))),
            )
            ->shouldBeCalled()
        ;

        self::assertInstanceOf(ErrorResponse::class, $this->saferpayClient->capture($payment->reveal()));
    }

    public function test_it_handles_failed_capture_request_with_content_different_than_json(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $response = $this->prophesize(ResponseInterface::class);

        $payload = $this->getExampleCapturePayload();
        $this->saferpayClientBodyFactory->createForCapture($payment)->willReturn($payload);
        $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['transaction_id' => '123456789']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $this->client
            ->request(
                'POST',
                'https://test.saferpay.com/api/Payment/v1/Transaction/Capture',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode('USERNAME:PASSWORD'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($payload),
                ],
            )
            ->shouldBeCalled()
            ->willReturn($response)
        ;

        $response->getStatusCode()->willReturn(402);
        $response->getHeaders(false)->willReturn(['content-type' => ['text/html']]);
        $response->getContent(false)->willReturn('Non JSON response content');

        $this->paymentEventDispatcher
            ->dispatchCaptureFailedEvent(
                $payment,
                'Payment/v1/Transaction/Capture',
                $payload,
                ErrorResponse::generalError('Non JSON response content', 'Capture'),
            )
            ->shouldBeCalled()
        ;

        self::assertInstanceOf(ErrorResponse::class, $this->saferpayClient->capture($payment->reveal()));
    }

    public function test_it_performs_refund_request(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $response = $this->prophesize(ResponseInterface::class);

        $payload = [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Refund' => [
                'Amount' => [
                    'Value' => 10000,
                    'CurrencyCode' => 'CHF',
                ],
            ],
            'CaptureReference' => [
                'CaptureId' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c',
            ],
        ];

        $this->saferpayClientBodyFactory->createForRefund($payment)->willReturn($payload);
        $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['capture_id' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $this->client
            ->request(
                'POST',
                'https://test.saferpay.com/api/Payment/v1/Transaction/Refund',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode('USERNAME:PASSWORD'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($payload),
                ],
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(200);
        $response->getHeaders(false)->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleRefundResponse());

        $this->paymentEventDispatcher
            ->dispatchRefundSucceededEvent(
                $payment,
                'Payment/v1/Transaction/Refund',
                $payload,
                RefundResponse::fromArray(array_merge(['StatusCode' => 200], json_decode($this->getExampleRefundResponse(), true))),
            )
            ->shouldBeCalled()
        ;

        self::assertInstanceOf(RefundResponse::class, $this->saferpayClient->refund($payment->reveal()));
    }

    public function test_it_handles_failed_refund_request(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $response = $this->prophesize(ResponseInterface::class);

        $payload = [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Refund' => [
                'Amount' => [
                    'Value' => 10000,
                    'CurrencyCode' => 'CHF',
                ],
            ],
            'CaptureReference' => [
                'CaptureId' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c',
            ],
        ];

        $this->saferpayClientBodyFactory->createForRefund($payment)->willReturn($payload);
        $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['capture_id' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $this->client
            ->request(
                'POST',
                'https://test.saferpay.com/api/Payment/v1/Transaction/Refund',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode('USERNAME:PASSWORD'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($payload),
                ],
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(400);
        $response->getHeaders(false)->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleRefundFailedResponse());

        $this->paymentEventDispatcher
            ->dispatchRefundFailedEvent(
                $payment,
                'Payment/v1/Transaction/Refund',
                $payload,
                ErrorResponse::forRefund(array_merge(['StatusCode' => 400], json_decode($this->getExampleRefundFailedResponse(), true))),
            )
            ->shouldBeCalled()
        ;

        self::assertInstanceOf(ErrorResponse::class, $this->saferpayClient->refund($payment->reveal()));
    }

    public function test_it_handles_failed_refund_request_with_content_different_than_json(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $response = $this->prophesize(ResponseInterface::class);

        $payload = [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Refund' => [
                'Amount' => [
                    'Value' => 10000,
                    'CurrencyCode' => 'CHF',
                ],
            ],
            'CaptureReference' => [
                'CaptureId' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c',
            ],
        ];

        $this->saferpayClientBodyFactory->createForRefund($payment)->willReturn($payload);
        $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['capture_id' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $this->client
            ->request(
                'POST',
                'https://test.saferpay.com/api/Payment/v1/Transaction/Refund',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode('USERNAME:PASSWORD'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($payload),
                ],
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(400);
        $response->getHeaders(false)->willReturn(['content-type' => ['text/html']]);
        $response->getContent(false)->willReturn('Non JSON response content');

        $this->paymentEventDispatcher
            ->dispatchRefundFailedEvent(
                $payment,
                'Payment/v1/Transaction/Refund',
                $payload,
                ErrorResponse::generalError('Non JSON response content', 'Refund'),
            )
            ->shouldBeCalled()
        ;

        self::assertInstanceOf(ErrorResponse::class, $this->saferpayClient->refund($payment->reveal()));
    }

    public function test_it_performs_get_terminal_request(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $response = $this->prophesize(ResponseInterface::class);

        $this->saferpayClientBodyFactory->provideHeadersForTerminal()->willReturn([
            'Saferpay-ApiVersion' => '1.33',
            'Saferpay-RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
        ]);
        $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'customer_id' => 'CUSTOMER_ID',
            'terminal_id' => 'TERMINAL_ID',
            'sandbox' => true,
        ]);

        $this->client
            ->request(
                'GET',
                'https://test.saferpay.com/api/rest/customers/CUSTOMER_ID/terminals/TERMINAL_ID',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode('USERNAME:PASSWORD'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Saferpay-ApiVersion' => '1.33',
                        'Saferpay-RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                    ],
                    'body' => json_encode([]),
                ],
            )
            ->shouldBeCalled()
            ->willReturn($response)
        ;

        $response->getStatusCode()->willReturn(200);
        $response->getHeaders(false)->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleTerminalResponse());

        self::assertEquals([
            'StatusCode' => '200',
            'TerminalId' => 'TERMINAL_ID',
            'Type' => 'ECOM',
            'PaymentMethods' => [[
                'PaymentMethod' => 'TWINT',
                'Currencies' => ['CHF'],
                'LogoUrl' => 'https://test.saferpay.com/static/logo/twint.svg',
            ], [
                'PaymentMethod' => 'VISA',
                'Currencies' => ['EUR', 'CHF', 'USD'],
                'LogoUrl' => 'https://test.saferpay.com/static/logo/visa.svg',
            ]],
        ], $this->saferpayClient->getTerminal($gatewayConfig->reveal()));
    }

    public function test_it_logs_exceptions_if_something_fails(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);

        $this->saferpayClientBodyFactory->provideHeadersForTerminal()->willReturn([
            'Saferpay-ApiVersion' => '1.33',
            'Saferpay-RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
        ]);
        $this->saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'customer_id' => 'CUSTOMER_ID',
            'terminal_id' => 'TERMINAL_ID',
            'sandbox' => true,
        ]);

        $this->client
            ->request(
                'GET',
                'https://test.saferpay.com/api/rest/customers/CUSTOMER_ID/terminals/TERMINAL_ID',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode('USERNAME:PASSWORD'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Saferpay-ApiVersion' => '1.33',
                        'Saferpay-RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                    ],
                    'body' => json_encode([]),
                ],
            )
            ->willThrow(new TimeoutException('Timeout'))
        ;

        $this->logger->error('Timeout')->shouldBeCalled();

        self::assertSame(
            ['error' => 'Timeout', 'StatusCode' => Response::HTTP_INTERNAL_SERVER_ERROR],
            $this->saferpayClient->getTerminal($gatewayConfig->reveal()),
        );
    }

    public function getExampleAuthorizePayload(): array
    {
        return [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'TerminalId' => 'TERMINAL-ID',
            'Payment' => [
                'Amount' => [
                    'Value' => 10000,
                    'CurrencyCode' => 'CHF',
                ],
                'OrderId' => '000000001',
                'Description' => 'Payment for order 000000001',
            ],
            'ReturnUrl' => [
                'Url' => 'https://example.com/after',
            ],
        ];
    }

    public function getExampleAuthorizeErrorResponse(): string
    {
        return <<<RESPONSE
        {
            "ResponseHeader": {
                "SpecVersion": "1.33",
                "RequestId": "3358af17-35c1-4165-a343-c1c86a320f3b"
            },
            "Behavior": "DO_NOT_RETRY",
            "ErrorName": "AUTHENTICATION_FAILED",
            "ErrorMessage": "Unable to authenticate request",
            "ErrorDetail": [
                "Invalid credentials"
            ]
        }
        RESPONSE;
    }

    public function getExampleCapturePayload(): array
    {
        return [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'TransactionReference' => [
                'TransactionId' => '123456789',
            ],
        ];
    }

    public function getExampleCaptureErrorResponse(): string
    {
        return <<<RESPONSE
        {
            "ResponseHeader": {
                "SpecVersion": "1.33",
                "RequestId": "123"
            },
            "Behavior": "DO_NOT_RETRY",
            "ErrorName": "TRANSACTION_NOT_FOUND",
            "ErrorMessage": "Transaction not found"
        }
        RESPONSE;
    }

    private function getExampleAuthorizeResponse(): string
    {
        return <<<RESPONSE
        {
          "ResponseHeader": {
            "SpecVersion": "1.33",
            "RequestId": "abc123"
          },
          "Token": "234uhfh78234hlasdfh8234e1234",
          "Expiration": "2015-01-30T12:45:22.258+01:00",
          "RedirectUrl": "https://www.saferpay.com/vt2/api/..."
        }
        RESPONSE;
    }

    private function getExampleAssertResponse(): string
    {
        return <<<RESPONSE
        {
          "ResponseHeader": {
            "SpecVersion": "1.33",
            "RequestId": "some-id"
          },
          "Transaction": {
            "Type": "PAYMENT",
            "Status": "AUTHORIZED",
            "Id": "723n4MAjMdhjSAhAKEUdA8jtl9jb",
            "Date": "2015-01-30T12:45:22.258+01:00",
            "Amount": {
              "Value": "100",
              "CurrencyCode": "CHF"
            },
            "OrderId": "000000001",
            "AcquirerName": "Saferpay Test Card",
            "AcquirerReference": "000000",
            "SixTransactionReference": "0:0:3:723n4MAjMdhjSAhAKEUdA8jtl9jb",
            "ApprovalCode": "012345",
            "IssuerReference": null
          },
          "PaymentMeans": {
            "Brand": {
              "PaymentMethod": "VISA",
              "Name": "VISA Saferpay Test"
            },
            "DisplayText": "9123 45xx xxxx 1234",
            "Card": {
              "MaskedNumber": "912345xxxxxx1234",
              "ExpYear": 2015,
              "ExpMonth": 9,
              "HolderName": "Max Mustermann",
              "CountryCode": "CH"
            }
          },
          "Liability": {
            "LiabilityShift": true,
            "LiableEntity": "THREEDS",
            "ThreeDs": {
              "Authenticated": true,
              "LiabilityShift": true,
              "Xid": "ARkvCgk5Y1t/BDFFXkUPGX9DUgs="
            }
          }
        }
        RESPONSE;
    }

    private function getExampleAssertFailedResponse(): string
    {
        return <<<RESPONSE
        {
          "ResponseHeader": {
            "SpecVersion": "1.33",
            "RequestId": "abc123"
          },
          "ErrorName": "CANNOT_ASSERT_PAYMENT",
          "ErrorMessage": "Payment cannot be asserted",
          "Behavior": "ABORT",
          "TransactionId": "723n4MAjMdhjSAhAKEUdA8jtl9jb",
          "OrderId": "12345",
          "PayerMessage": "Payment cannot be asserted"
        }
        RESPONSE;
    }

    private function getExampleCaptureResponse(): string
    {
        return <<<RESPONSE
        {
          "ResponseHeader": {
            "SpecVersion": "1.33",
            "RequestId": "abc123"
          },
          "CaptureId": "723n4MAjMdhjSAhAKEUdA8jtl9jb",
          "Status": "CAPTURED",
          "Date": "2015-01-30T12:45:22.258+01:00"
        }
        RESPONSE;
    }

    private function getExampleRefundResponse(): string
    {
        return <<<RESPONSE
        {
           "ResponseHeader":{
              "SpecVersion":"1.33",
              "RequestId":"1f97328d-651f-44be-94d6-fbb0a4d2f117"
           },
           "Transaction":{
              "Type":"REFUND",
              "Status":"AUTHORIZED",
              "Id":"Q7Wf4lb07WtbtAEd6j30bx4UhdvA",
              "Date":"2023-04-26T10:41:53.792+02:00",
              "Amount":{
                 "Value":"10000",
                 "CurrencyCode":"CHF"
              },
              "AcquirerName":"VISA Saferpay Test",
              "AcquirerReference":"50953026375",
              "SixTransactionReference":"0:0:3:Q7Wf4lb07WtbtAEd6j30bx4UhdvA",
              "ApprovalCode":"283702",
              "IssuerReference":{
                 "TransactionStamp":"3797496535630697360974"
              }
           },
           "PaymentMeans":{
              "Brand":{
                 "PaymentMethod":"VISA",
                 "Name":"VISA"
              },
              "DisplayText":"xxxx xxxx xxxx 0007",
              "Card":{
                 "MaskedNumber":"xxxxxxxxxxxx0007",
                 "ExpYear":2023,
                 "ExpMonth":4,
                 "HolderName":"Yamada Taro",
                 "CountryCode":"JP"
              }
           }
        }
        RESPONSE;
    }

    private function getExampleRefundFailedResponse(): string
    {
        return <<<RESPONSE
        {
          "ResponseHeader": {
            "SpecVersion": "1.33",
            "RequestId": "abc123"
          },
          "ErrorName": "CANNOT_REFUND_PAYMENT",
          "ErrorMessage": "Payment cannot be refunded",
          "Behavior": "ABORT",
          "TransactionId": "723n4MAjMdhjSAhAKEUdA8jtl9jb",
          "OrderId": "12345",
          "PayerMessage": "Payment cannot be refunded"
        }
        RESPONSE;
    }

    private function getExampleTerminalResponse(): string
    {
        return <<<RESPONSE
        {
          "TerminalId": "TERMINAL_ID",
          "Type": "ECOM",
          "PaymentMethods": [
            {
              "PaymentMethod": "TWINT",
              "Currencies": [
                "CHF"
              ],
              "LogoUrl": "https://test.saferpay.com/static/logo/twint.svg"
            },
            {
              "PaymentMethod": "VISA",
              "Currencies": [
                "EUR",
                "CHF",
                "USD"
              ],
              "LogoUrl": "https://test.saferpay.com/static/logo/visa.svg"
            }
          ]
        }
        RESPONSE;
    }
}
