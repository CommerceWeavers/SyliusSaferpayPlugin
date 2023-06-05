<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAssertionFailed;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAssertionSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAuthorizationSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentCaptureSucceeded;
use Doctrine\Persistence\ObjectManager;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class SaferpayPaymentEventContext implements Context
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private ObjectManager $paymentMethodObjectManager,
    ) {
    }

    /**
     * @Given /^(the payment method)'s debug mode is (enabled|disabled)/
     */
    public function thePaymentMethodsDebugModeIs(PaymentMethodInterface $paymentMethod, string $debugMode): void
    {
        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        $gatewayConfig->setConfig(
            array_merge(
                $gatewayConfig->getConfig(),
                ['debug' => $debugMode === 'enabled'],
            ),
        );

        $this->paymentMethodObjectManager->flush();
    }

    /**
     * @Given /^(the order) payment failed on the assertion step$/
     */
    public function thePaymentFailedOnTheFirstTryAfterTheAuthorizeStep(OrderInterface $order): void
    {
        /** @var PaymentInterface $payment */
        $payment = $order->getPayments()->first();
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->commandBus->dispatch(new PaymentAuthorizationSucceeded(
            $paymentId,
            'https://example.com/saferpay/authorize',
            [],
            [],
        ));

        $this->commandBus->dispatch(new PaymentAssertionFailed(
            $paymentId,
            'https://example.com/saferpay/assert',
            [],
            [],
        ));
    }

    /**
     * @Given /^(the order) has been paid successfully with Saferpay payment method on the second try$/
     * @Given /^(the order) has been paid successfully with Saferpay payment method$/
     */
    public function theSystemHasBeenNotifiedAboutPaymentOnThisOrder(OrderInterface $order): void
    {
        /** @var PaymentInterface $payment */
        $payment = $order->getPayments()->first();
        /** @var int $paymentId */
        $paymentId = $payment->getId();

        $this->commandBus->dispatch(new PaymentAuthorizationSucceeded(
            $paymentId,
            'https://example.com/saferpay/authorize',
            [],
            [],
        ));

        $this->commandBus->dispatch(new PaymentAssertionSucceeded(
            $paymentId,
            'https://example.com/saferpay/assert',
            [],
            [],
        ));

        $this->commandBus->dispatch(new PaymentCaptureSucceeded(
            $paymentId,
            'https://example.com/saferpay/capture',
            [],
            [],
        ));
    }
}
