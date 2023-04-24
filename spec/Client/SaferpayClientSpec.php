<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientBodyFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Resolver\SaferpayApiBaseUrlResolverInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class SaferpayClientSpec extends ObjectBehavior
{
    function let(
        ClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver
    ): void {
        $this->beConstructedWith($client, $saferpayClientBodyFactory, $saferpayApiBaseUrlResolver);
    }

    function it_implements_saferpay_client_interface(): void
    {
        $this->shouldHaveType(SaferpayClientInterface::class);
    }

    function it_performs_authorize_request(
        ClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        OrderInterface $order,
        TokenInterface $token,
        ResponseInterface $response,
        StreamInterface $body,
    ): void {
        $saferpayClientBodyFactory->createForAuthorize($payment, $token)->willReturn([
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
        ]);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'sandbox' => true,
        ]);
        $payment->getOrder()->willReturn($order);
        $payment->getAmount()->willReturn(10000);
        $payment->getCurrencyCode()->willReturn('CHF');
        $order->getNumber()->willReturn('000000001');

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
                    'body' => json_encode([
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
                    ]),
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response)
        ;

        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn($body);
        $body->getContents()->willReturn($this->getExampleAuthorizeResponse());

        $this->authorize($payment, $token)->shouldBeAnInstanceOf(AuthorizeResponse::class);
    }

    function it_performs_assert_request(
        ClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
        StreamInterface $body,
    ): void {
        $saferpayClientBodyFactory->createForAssert($payment)->willReturn([
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Token' => 'TOKEN',
        ]);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

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
                    'body' => json_encode([
                        'RequestHeader' => [
                            'SpecVersion' => '1.33',
                            'CustomerId' => 'CUSTOMER-ID',
                            'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                            'RetryIndicator' => 0,
                        ],
                        'Token' => 'TOKEN',
                    ]),
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response)
        ;

        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn($body);
        $body->getContents()->willReturn($this->getExampleAssertResponse());

        $this->assert($payment)->shouldBeAnInstanceOf(AssertResponse::class);
    }

    function it_handles_an_exception_during_assert_request(
        ClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
        StreamInterface $body,
        RequestException $exception,
    ): void {
        $saferpayClientBodyFactory->createForAssert($payment)->willReturn([
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Token' => 'TOKEN',
        ]);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

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
                    'body' => json_encode([
                        'RequestHeader' => [
                            'SpecVersion' => '1.33',
                            'CustomerId' => 'CUSTOMER-ID',
                            'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                            'RetryIndicator' => 0,
                        ],
                        'Token' => 'TOKEN',
                    ]),
                ]
            )
            ->shouldBeCalled()
            ->willThrow($exception->getWrappedObject())
        ;

        $exception->getResponse()->willReturn($response);
        $response->getStatusCode()->willReturn(402);
        $response->getBody()->willReturn($body);
        $body->getContents()->willReturn($this->getExampleAssertErrorResponse());

        $this->assert($payment)->shouldBeAnInstanceOf(AssertResponse::class);
    }

    function it_performs_capture_request(
        ClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
        StreamInterface $body,
    ): void {
        $saferpayClientBodyFactory->createForCapture($payment)->willReturn([
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'TransactionReference' => [
                'TransactionId' => '123456789',
            ],
        ]);
        $saferpayApiBaseUrlResolver->resolve($gatewayConfig)->willReturn('https://test.saferpay.com/api/');

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
                    'body' => json_encode([
                        'RequestHeader' => [
                            'SpecVersion' => '1.33',
                            'CustomerId' => 'CUSTOMER-ID',
                            'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                            'RetryIndicator' => 0,
                        ],
                        'TransactionReference' => [
                            'TransactionId' => '123456789',
                        ],
                    ]),
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response)
        ;

        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn($body);
        $body->getContents()->willReturn($this->getExampleCaptureResponse());

        $this->capture($payment)->shouldBeAnInstanceOf(CaptureResponse::class);
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
            "AcquirerName": "Saferpay Test Card",
            "AcquirerReference": "000000",
            "SixTransactionReference": "0:0:3:723n4MAjMdhjSAhAKEUdA8jtl9jb",
            "ApprovalCode": "012345"
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

    private function getExampleAssertErrorResponse(): string
    {
        return <<<RESPONSE
        {
          "ResponseHeader": {
            "SpecVersion": "1.33",
            "RequestId": "some-id"
          },
          "Behavior":"DO_NOT_RETRY",
          "ErrorName":"3DS_AUTHENTICATION_FAILED",
          "ErrorMessage":"3D-Secure authentication failed",
          "TransactionId":"Q3hd5IbzlnKpvAICv2QdA72QlA1b",
          "PayerMessage":"Card holder information -> Failed",
          "OrderId":"000000042"
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
}
