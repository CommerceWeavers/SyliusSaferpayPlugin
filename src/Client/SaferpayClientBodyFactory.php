<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Provider\UuidProviderInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class SaferpayClientBodyFactory implements SaferpayClientBodyFactoryInterface
{
    private const SPEC_VERSION = '1.33';

    public function __construct(
        private UuidProviderInterface $uuidProvider,
    ) {
    }

    public function createForAuthorize(PaymentInterface $payment, TokenInterface $token): array
    {
        $paymentMethod = $payment->getMethod();
        Assert::notNull($paymentMethod);
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);
        $terminalId = (string) $gatewayConfig->getConfig()['terminal_id'];

        $order = $payment->getOrder();
        Assert::notNull($order);
        /** @var string $orderNumber */
        $orderNumber = $order->getNumber();

        return array_merge($this->provideBodyRequestHeader($gatewayConfig), [
            'TerminalId' => $terminalId,
            'Payment' => [
                'Amount' => [
                    'Value' => $payment->getAmount(),
                    'CurrencyCode' => $payment->getCurrencyCode(),
                ],
                'OrderId' => $orderNumber,
                'Description' => sprintf('Payment for order %s', $orderNumber),
            ],
            'ReturnUrl' => [
                'Url' => $token->getAfterUrl(),
            ],
        ]);
    }

    public function createForAssert(PaymentInterface $payment): array
    {
        $paymentMethod = $payment->getMethod();
        Assert::notNull($paymentMethod);
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);

        return array_merge($this->provideBodyRequestHeader($gatewayConfig), [
            'Token' => $payment->getDetails()['saferpay_token'],
        ]);
    }

    public function createForCapture(PaymentInterface $payment): array
    {
        $paymentMethod = $payment->getMethod();
        Assert::notNull($paymentMethod);
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);

        return array_merge($this->provideBodyRequestHeader($gatewayConfig), [
            'TransactionReference' => [
                'TransactionId' => $payment->getDetails()['transaction_id'],
            ],
        ]);
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
}
