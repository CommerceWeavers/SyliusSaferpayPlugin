<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientBodyFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\EventDispatcher\PaymentEventDispatcherInterface;
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
        ClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        OrderInterface $order,
        TokenInterface $token,
        ResponseInterface $response,
        StreamInterface $body,
    ): void {
        $payload = [
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
                    'body' => json_encode($payload),
                ]
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn($body);
        $body->getContents()->willReturn($this->getExampleAuthorizeResponse());

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

    function it_performs_assert_request(
        ClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
        StreamInterface $body,
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
        $response->getBody()->willReturn($body);
        $body->getContents()->willReturn($this->getExampleAssertResponse());

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

    function it_handles_an_exception_during_assert_request(
        ClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
        StreamInterface $body,
        RequestException $exception,
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
            ->willThrow($exception->getWrappedObject());

        $exception->getResponse()->willReturn($response);
        $response->getStatusCode()->willReturn(402);
        $response->getBody()->willReturn($body);
        $body->getContents()->willReturn($this->getExampleAssertErrorResponse());

        $paymentEventDispatcher
            ->dispatchAssertionFailedEvent(
                $payment,
                'Payment/v1/PaymentPage/Assert',
                $payload,
                AssertResponse::fromArray(array_merge(['StatusCode' => 402], json_decode($this->getExampleAssertErrorResponse(), true)))
            )
            ->shouldBeCalled()
        ;

        $this->assert($payment)->shouldBeAnInstanceOf(AssertResponse::class);
    }

    function it_performs_capture_request(
        ClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
        StreamInterface $body,
    ): void {
        $payload = [
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
        $response->getBody()->willReturn($body);
        $body->getContents()->willReturn($this->getExampleCaptureResponse());

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

    function it_performs_refund_request(
        ClientInterface $client,
        SaferpayClientBodyFactoryInterface $saferpayClientBodyFactory,
        SaferpayApiBaseUrlResolverInterface $saferpayApiBaseUrlResolver,
        PaymentEventDispatcherInterface $paymentEventDispatcher,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
        StreamInterface $body,
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
        $response->getBody()->willReturn($body);
        $body->getContents()->willReturn($this->getExampleRefundResponse());

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
}
