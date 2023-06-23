<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\UuidProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Routing\Generator\WebhookRouteGeneratorInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Webmozart\Assert\Assert;

final class SaferpayClientBodyFactory implements SaferpayClientBodyFactoryInterface
{
    private const SPEC_VERSION = '1.33';

    private const COMMERCE_WEAVERS_SYLIUS_SAFERPAY_WEBHOOK = 'commerce_weavers_sylius_saferpay_webhook';

    public function __construct(
        private UuidProviderInterface $uuidProvider,
        private TokenProviderInterface $tokenProvider,
        private WebhookRouteGeneratorInterface $webhookRouteGenerator,
    ) {
    }

    public function createForAuthorize(PaymentInterface $payment, TokenInterface $token): array
    {
        $order = $payment->getOrder();
        Assert::notNull($order);
        /** @var string $orderNumber */
        $orderNumber = $order->getNumber();

        $gatewayConfig = $this->provideGatewayConfig($payment);
        $config = $gatewayConfig->getConfig();
        $terminalId = (string) $config['terminal_id'];
        /** @var array $allowedPaymentMethods */
        $allowedPaymentMethods = $config['allowed_payment_methods'] ?? [];

        $webhookToken = $this->tokenProvider->provideForWebhook($payment, self::COMMERCE_WEAVERS_SYLIUS_SAFERPAY_WEBHOOK);
        $notificationUrl = $this->webhookRouteGenerator->generate($webhookToken->getHash(), (string) $order->getTokenValue());

        return array_merge($this->provideBodyRequestHeader($gatewayConfig), [
            'TerminalId' => $terminalId,
            'Payment' => [
                'Amount' => [
                    'Value' => $payment->getAmount(),
                    'CurrencyCode' => $payment->getCurrencyCode(),
                ],
                'OrderId' => $orderNumber,
                'Description' => sprintf('Payment for order #%s', $orderNumber),
            ],
            'PaymentMethods' => array_values($allowedPaymentMethods),
            'Notification' => [
                'PayerEmail' => $payment->getOrder()?->getCustomer()?->getEmail(),
                'SuccessNotifyUrl' => $notificationUrl,
                'FailNotifyUrl' => $notificationUrl,
            ],
            'ReturnUrl' => [
                'Url' => $token->getAfterUrl(),
            ],
        ]);
    }

    public function createForAssert(PaymentInterface $payment): array
    {
        return array_merge($this->provideBodyRequestHeader($this->provideGatewayConfig($payment)), [
            'Token' => $payment->getDetails()['saferpay_token'],
        ]);
    }

    public function createForCapture(PaymentInterface $payment): array
    {
        return array_merge($this->provideBodyRequestHeader($this->provideGatewayConfig($payment)), [
            'TransactionReference' => [
                'TransactionId' => $payment->getDetails()['transaction_id'],
            ],
        ]);
    }

    public function createForRefund(PaymentInterface $payment): array
    {
        return array_merge($this->provideBodyRequestHeader($this->provideGatewayConfig($payment)), [
            'Refund' => [
                'Amount' => [
                    'Value' => $payment->getAmount(),
                    'CurrencyCode' => $payment->getCurrencyCode(),
                ],
            ],
            'CaptureReference' => [
                'CaptureId' => $payment->getDetails()['capture_id'],
            ],
        ]);
    }

    public function provideHeadersForTerminal(): array
    {
        return [
            'Saferpay-ApiVersion' => self::SPEC_VERSION,
            'Saferpay-RequestId' => $this->uuidProvider->provide(),
        ];
    }

    private function provideBodyRequestHeader(GatewayConfigInterface $gatewayConfig): array
    {
        $customerId = (string) $gatewayConfig->getConfig()['customer_id'];

        return [
            'RequestHeader' => [
                'SpecVersion' => self::SPEC_VERSION,
                'CustomerId' => $customerId,
                'RequestId' => $this->uuidProvider->provide(),
                'RetryIndicator' => 0,
            ],
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
