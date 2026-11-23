<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientBodyFactory;
use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientBodyFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\UuidProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Routing\Generator\WebhookRouteGeneratorInterface;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class SaferpayClientBodyFactoryTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $uuidProvider;

    private ObjectProphecy $tokenProvider;

    private ObjectProphecy $webhookRouteGenerator;

    private SaferpayClientBodyFactory $factory;

    protected function setUp(): void
    {
        $this->uuidProvider = $this->prophesize(UuidProviderInterface::class);
        $this->tokenProvider = $this->prophesize(TokenProviderInterface::class);
        $this->webhookRouteGenerator = $this->prophesize(WebhookRouteGeneratorInterface::class);

        $this->factory = new SaferpayClientBodyFactory(
            $this->uuidProvider->reveal(),
            $this->tokenProvider->reveal(),
            $this->webhookRouteGenerator->reveal(),
        );
    }

    public function test_it_implements_saferpay_client_body_factory_interface(): void
    {
        self::assertInstanceOf(SaferpayClientBodyFactoryInterface::class, $this->factory);
    }

    public function test_it_creates_body_for_authorize_request(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $order = $this->prophesize(OrderInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $webhookToken = $this->prophesize(TokenInterface::class);
        $customer = $this->prophesize(CustomerInterface::class);

        $this->uuidProvider->provide()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

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
        $order->getTokenValue()->willReturn('TOKEN');
        $customer->getEmail()->willReturn('test@example.com');

        $token->getAfterUrl()->willReturn('https://example.com/after');

        $this->tokenProvider->provideForWebhook($payment, 'commerce_weavers_sylius_saferpay_webhook')->willReturn($webhookToken);
        $webhookToken->getHash()->willReturn('webhook_hash');
        $this->webhookRouteGenerator->generate('webhook_hash', 'TOKEN')->willReturn('https://example.com/webhook');

        self::assertSame([
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
                'SuccessNotifyUrl' => 'https://example.com/webhook',
                'FailNotifyUrl' => 'https://example.com/webhook',
            ],
            'ReturnUrl' => [
                'Url' => 'https://example.com/after',
            ],
        ], $this->factory->createForAuthorize($payment->reveal(), $token->reveal()));
    }

    public function test_it_creates_body_for_assert_request(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);

        $this->uuidProvider->provide()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        $payment->getDetails()->willReturn(['saferpay_token' => 'TOKEN']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'customer_id' => 'CUSTOMER-ID',
            'terminal_id' => 'TERMINAL-ID',
        ]);

        self::assertSame([
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Token' => 'TOKEN',
        ], $this->factory->createForAssert($payment->reveal()));
    }

    public function test_it_creates_body_for_capture_request(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);

        $this->uuidProvider->provide()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        $payment->getDetails()->willReturn(['transaction_id' => '123456789']);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'customer_id' => 'CUSTOMER-ID',
        ]);

        self::assertSame([
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'TransactionReference' => [
                'TransactionId' => '123456789',
            ],
        ], $this->factory->createForCapture($payment->reveal()));
    }

    public function test_it_creates_body_for_refund_request(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);

        $this->uuidProvider->provide()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        $payment->getDetails()->willReturn(['capture_id' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c']);
        $payment->getAmount()->willReturn(10000);
        $payment->getCurrencyCode()->willReturn('CHF');
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'customer_id' => 'CUSTOMER-ID',
        ]);

        self::assertSame([
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
        ], $this->factory->createForRefund($payment->reveal()));
    }

    public function test_it_provides_headers_for_terminal_request(): void
    {
        $this->uuidProvider->provide()->willReturn('b27de121-ffa0-4f1d-b7aa-b48109a88486');

        self::assertSame([
            'Saferpay-ApiVersion' => '1.33',
            'Saferpay-RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
        ], $this->factory->provideHeadersForTerminal());
    }
}
