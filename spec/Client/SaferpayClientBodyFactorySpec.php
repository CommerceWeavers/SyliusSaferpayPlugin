<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientBodyFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\UuidProviderInterface;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class SaferpayClientBodyFactorySpec extends ObjectBehavior
{
    function let(UuidProviderInterface $uuidProvider): void
    {
        $this->beConstructedWith($uuidProvider);
    }

    function it_implements_saferpay_client_body_factory_interface(): void
    {
        $this->shouldHaveType(SaferpayClientBodyFactoryInterface::class);
    }

    function it_creates_body_for_authorize_request(
        UuidProviderInterface $uuidProvider,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        OrderInterface $order,
        TokenInterface $token,
        CustomerInterface $customer
    ): void {
        $uuidProvider->provide()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'customer_id' => 'CUSTOMER-ID',
            'terminal_id' => 'TERMINAL-ID',
            'allowed_payment_methods' => ['VISA', 'MASTERCARD'],
        ]);
        $payment->getOrder()->willReturn($order);
        $payment->getAmount()->willReturn(10000);
        $payment->getCurrencyCode()->willReturn('CHF');
        $order->getNumber()->willReturn('000000001');
        $order->getCustomer()->willReturn($customer);
        $customer->getEmail()->willReturn('test@example.com');

        $token->getAfterUrl()->willReturn('https://example.com/after');

        $this->createForAuthorize($payment, $token)->shouldReturn([
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
                'Description' => 'Payment for order #000000001',
            ],
            'PaymentMethods' => ['VISA', 'MASTERCARD'],
            'Notification' => [
                'PayerEmail' => 'test@example.com',
            ],
            'ReturnUrl' => [
                'Url' => 'https://example.com/after',
            ],
        ]);
    }

    function it_creates_body_for_assert_request(
        UuidProviderInterface $uuidProvider,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $uuidProvider->provide()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        $payment->getDetails()->willReturn(['saferpay_token' => 'TOKEN']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'customer_id' => 'CUSTOMER-ID',
            'terminal_id' => 'TERMINAL-ID',
        ]);

        $this->createForAssert($payment)->shouldReturn([
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Token' => 'TOKEN',
        ]);
    }

    function it_creates_body_for_capture_request(
        UuidProviderInterface $uuidProvider,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $uuidProvider->provide()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        $payment->getDetails()->willReturn(['transaction_id' => '123456789']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'customer_id' => 'CUSTOMER-ID',
        ]);

        $this->createForCapture($payment)->shouldReturn([
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
    }

    function it_creates_body_for_refund_request(
        UuidProviderInterface $uuidProvider,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $uuidProvider->provide()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        $payment->getDetails()->willReturn(['capture_id' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c']);
        $payment->getAmount()->willReturn(10000);
        $payment->getCurrencyCode()->willReturn('CHF');
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'customer_id' => 'CUSTOMER-ID',
        ]);

        $this->createForRefund($payment)->shouldReturn([
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
        ]);
    }
}
