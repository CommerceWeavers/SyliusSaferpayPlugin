<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\UuidProviderInterface;
use GuzzleHttp\ClientInterface;
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
    function let(ClientInterface $client, UuidProviderInterface $uuidProvider): void
    {
        $this->beConstructedWith($client, $uuidProvider, 'https://test.saferpay.com/api/Payment/v1/');
    }

    function it_implements_saferpay_client_interface(): void
    {
        $this->shouldHaveType(SaferpayClientInterface::class);
    }

    function it_performs_authorize_request(
        ClientInterface $client,
        UuidProviderInterface $uuidProvider,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        OrderInterface $order,
        TokenInterface $token,
        ResponseInterface $response,
        StreamInterface $body,
    ): void {
        $uuidProvider->provide()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'customerId' => 'CUSTOMER-ID',
            'terminalId' => 'TERMINAL-ID',
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
        $body->getContents()->willReturn('{"status": "OK"}');

        $this->authorize($payment, $token)->shouldReturn(['status' => 'OK']);;
    }

    function it_performs_capture_request(
        ClientInterface $client,
        UuidProviderInterface $uuidProvider,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        ResponseInterface $response,
        StreamInterface $body,
    ): void {
        $uuidProvider->provide()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        $payment->getDetails()->willReturn(['transaction_id' => '123456789']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'customerId' => 'CUSTOMER-ID',
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
        $body->getContents()->willReturn('{"status": "OK"}');

        $this->capture($payment)->shouldReturn(['status' => 'OK']);;
    }
}
