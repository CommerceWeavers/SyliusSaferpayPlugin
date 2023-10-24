<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Client;

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
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class SaferpayClientSpec extends ObjectBehavior
{
    function let(
        HttpClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
    ): void {
        $this->beConstructedWith($client, $saferpayClientBodyFactory, $saferpayApiBaseUrlResolver, $paymentEventDispatcher);
    }

    function it_implements_saferpay_client_interface(): void
    {
        $this->shouldHaveType(SaferpayClientInterface::class);
    }

    function it_performs_authorize_request(
        HttpClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        TokenInterface $token,
        ResponseInterface $response,
    ): void {
        $payload = $this->getExampleAuthorizePayload();
        $saferpayClientBodyFactory->createForAuthorize($payment, $token)->willReturn($payload);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $client
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
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(200);
        $response->getHeaders()->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleAuthorizeResponse());

        $paymentEventDispatcher
            ->dispatchAuthorizationSucceededEvent(
                $payment,
                'Payment/v1/PaymentPage/Initialize',
                $payload,
                AuthorizeResponse::fromArray(array_merge(['StatusCode' => 200], json_decode($this->getExampleAuthorizeResponse(), true)))
            )
            ->shouldBeCalled()
        ;

        $this->authorize($payment, $token)->shouldBeAnInstanceOf(AuthorizeResponse::class);
    }

    function it_dispatches_a_failed_event_once_the_authorization_fails(
        HttpClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        TokenInterface $token,
        ResponseInterface $response,
    ): void {
        $payload = $this->getExampleAuthorizePayload();
        $saferpayClientBodyFactory->createForAuthorize($payment, $token)->willReturn($payload);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $token->getAfterUrl()->willReturn('https://example.com/after');

        $client
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
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(402);
        $response->getHeaders()->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleAuthorizeErrorResponse());

        $paymentEventDispatcher
            ->dispatchAuthorizationFailedEvent(
                $payment,
                'Payment/v1/PaymentPage/Initialize',
                $payload,
                ErrorResponse::forAuthorize(array_merge(['StatusCode' => 402], json_decode($this->getExampleAuthorizeErrorResponse(), true)))
            )
            ->shouldBeCalled()
        ;

        $this->authorize($payment, $token)->shouldBeAnInstanceOf(ErrorResponse::class);
    }

    function it_handles_failed_authorize_request_with_content_different_than_json(
        HttpClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        TokenInterface $token,
        ResponseInterface $response,
    ): void {
        $payload = $this->getExampleAuthorizePayload();
        $saferpayClientBodyFactory->createForAuthorize($payment, $token)->willReturn($payload);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $token->getAfterUrl()->willReturn('https://example.com/after');

        $client
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
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(402);
        $response->getHeaders()->willReturn(['content-type' => ['text/html']]);
        $response->getContent(false)->willReturn('Non JSON response content');

        $paymentEventDispatcher
            ->dispatchAuthorizationFailedEvent(
                $payment,
                'Payment/v1/PaymentPage/Initialize',
                $payload,
                ErrorResponse::generalError('Non JSON response content', 'Authorize'),
            )
            ->shouldBeCalled()
        ;

        $this->authorize($payment, $token)->shouldBeAnInstanceOf(ErrorResponse::class);
    }

    function it_performs_assert_request(
        HttpClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
    ): void {
        $payload = [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Token' => 'TOKEN',
        ];
        $saferpayClientBodyFactory->createForAssert($payment)->willReturn($payload);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['saferpay_token' => 'TOKEN']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $client
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
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(200);
        $response->getHeaders()->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleAssertResponse());

        $paymentEventDispatcher
            ->dispatchAssertionSucceededEvent(
                $payment,
                'Payment/v1/PaymentPage/Assert',
                $payload,
                AssertResponse::fromArray(array_merge(['StatusCode' => 200], json_decode($this->getExampleAssertResponse(), true)))
            )
            ->shouldBeCalled()
        ;

        $this->assert($payment)->shouldBeAnInstanceOf(AssertResponse::class);
    }

    function it_performs_failed_assert_request(
        HttpClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
    ): void {
        $payload = [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Token' => 'TOKEN',
        ];
        $saferpayClientBodyFactory->createForAssert($payment)->willReturn($payload);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['saferpay_token' => 'TOKEN']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $client
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
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(400);
        $response->getHeaders()->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleAssertFailedResponse());

        $paymentEventDispatcher
            ->dispatchAssertionFailedEvent(
                $payment,
                'Payment/v1/PaymentPage/Assert',
                $payload,
                ErrorResponse::forAssert(array_merge(['StatusCode' => 400], json_decode($this->getExampleAssertFailedResponse(), true)))
            )
            ->shouldBeCalled()
        ;

        $this->assert($payment)->shouldBeAnInstanceOf(ErrorResponse::class);
    }

    function it_handles_failed_assert_request_with_content_different_than_json(
        HttpClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
    ): void {
        $payload = [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Token' => 'TOKEN',
        ];
        $saferpayClientBodyFactory->createForAssert($payment)->willReturn($payload);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['saferpay_token' => 'TOKEN']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $client
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
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(400);
        $response->getHeaders()->willReturn(['content-type' => ['text/html']]);
        $response->getContent(false)->willReturn('Non JSON response content');

        $paymentEventDispatcher
            ->dispatchAssertionFailedEvent(
                $payment,
                'Payment/v1/PaymentPage/Assert',
                $payload,
                ErrorResponse::generalError('Non JSON response content', 'Assert'),
            )
            ->shouldBeCalled()
        ;

        $this->assert($payment)->shouldBeAnInstanceOf(ErrorResponse::class);
    }

    function it_performs_capture_request(
        HttpClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
    ): void {
        $payload = $this->getExampleCapturePayload();
        $saferpayClientBodyFactory->createForCapture($payment)->willReturn($payload);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['transaction_id' => '123456789']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $client
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
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(200);
        $response->getHeaders()->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleCaptureResponse());

        $paymentEventDispatcher
            ->dispatchCaptureSucceededEvent(
                $payment,
                'Payment/v1/Transaction/Capture',
                $payload,
                CaptureResponse::fromArray(array_merge(['StatusCode' => 200], json_decode($this->getExampleCaptureResponse(), true)))
            )
            ->shouldBeCalled()
        ;

        $this->capture($payment)->shouldBeAnInstanceOf(CaptureResponse::class);
    }

    function it_dispatches_a_failed_event_once_the_capture_fails(
        HttpClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
    ): void {
        $payload = $this->getExampleCapturePayload();
        $saferpayClientBodyFactory->createForCapture($payment)->willReturn($payload);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['transaction_id' => '123456789']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $client
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
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(402);
        $response->getHeaders()->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleCaptureErrorResponse());

        $paymentEventDispatcher
            ->dispatchCaptureFailedEvent(
                $payment,
                'Payment/v1/Transaction/Capture',
                $payload,
                ErrorResponse::forCapture(array_merge(['StatusCode' => 402], json_decode($this->getExampleCaptureErrorResponse(), true)))
            )
            ->shouldBeCalled()
        ;

        $this->capture($payment)->shouldBeAnInstanceOf(ErrorResponse::class);
    }

    function it_handles_failed_capture_request_with_content_different_than_json(
        HttpClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
    ): void {
        $payload = $this->getExampleCapturePayload();
        $saferpayClientBodyFactory->createForCapture($payment)->willReturn($payload);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['transaction_id' => '123456789']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $client
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
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response)
        ;

        $response->getStatusCode()->willReturn(402);
        $response->getHeaders()->willReturn(['content-type' => ['text/html']]);
        $response->getContent(false)->willReturn('Non JSON response content');

        $paymentEventDispatcher
            ->dispatchCaptureFailedEvent(
                $payment,
                'Payment/v1/Transaction/Capture',
                $payload,
                ErrorResponse::generalError('Non JSON response content', 'Capture'),
            )
            ->shouldBeCalled()
        ;

        $this->capture($payment)->shouldBeAnInstanceOf(ErrorResponse::class);
    }

    function it_performs_refund_request(
        HttpClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
    ): void {
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

        $saferpayClientBodyFactory->createForRefund($payment)->willReturn($payload);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['capture_id' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $client
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
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(200);
        $response->getHeaders()->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleRefundResponse());

        $paymentEventDispatcher
            ->dispatchRefundSucceededEvent(
                $payment,
                'Payment/v1/Transaction/Refund',
                $payload,
                RefundResponse::fromArray(array_merge(['StatusCode' => 200], json_decode($this->getExampleRefundResponse(), true)))
            )
            ->shouldBeCalled()
        ;

        $this->refund($payment)->shouldBeAnInstanceOf(RefundResponse::class);
    }

    function it_handles_failed_refund_request(
        HttpClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
    ): void {
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

        $saferpayClientBodyFactory->createForRefund($payment)->willReturn($payload);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['capture_id' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $client
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
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(400);
        $response->getHeaders()->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleRefundFailedResponse());

        $paymentEventDispatcher
            ->dispatchRefundFailedEvent(
                $payment,
                'Payment/v1/Transaction/Refund',
                $payload,
                ErrorResponse::forRefund(array_merge(['StatusCode' => 400], json_decode($this->getExampleRefundFailedResponse(), true)))
            )
            ->shouldBeCalled()
        ;

        $this->refund($payment)->shouldBeAnInstanceOf(ErrorResponse::class);
    }

    function it_handles_failed_refund_request_with_content_different_than_json(
        HttpClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
    ): void {
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

        $saferpayClientBodyFactory->createForRefund($payment)->willReturn($payload);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['capture_id' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);

        $client
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
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(400);
        $response->getHeaders()->willReturn(['content-type' => ['text/html']]);
        $response->getContent(false)->willReturn('Non JSON response content');

        $paymentEventDispatcher
            ->dispatchRefundFailedEvent(
                $payment,
                'Payment/v1/Transaction/Refund',
                $payload,
                ErrorResponse::generalError('Non JSON response content', 'Refund')
            )
            ->shouldBeCalled()
        ;

        $this->refund($payment)->shouldBeAnInstanceOf(ErrorResponse::class);
    }

    function it_performs_get_terminal_request(
        HttpClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
    ): void {
        $saferpayClientBodyFactory->provideHeadersForTerminal()->willReturn([
            'Saferpay-ApiVersion' => '1.33',
            'Saferpay-RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
        ]);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'customer_id' => 'CUSTOMER_ID',
            'terminal_id' => 'TERMINAL_ID',
            'sandbox' => true,
        ]);

        $client
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
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response)
        ;

        $response->getStatusCode()->willReturn(200);
        $response->getHeaders()->willReturn(['content-type' => ['application/json']]);
        $response->getContent(false)->willReturn($this->getExampleTerminalResponse());

        $this->getTerminal($gatewayConfig)->shouldBeLike([
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
        ]);
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

    /**
     * @return array
     */
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
}
