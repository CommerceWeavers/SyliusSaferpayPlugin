<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAssertionFailed;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAssertionSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAuthorizationSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentCaptureSucceeded;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\PaymentMethod\UpdatePageInterface;

final class SaferpayPaymentEventContext implements Context
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private UpdatePageInterface $updatePage,
    ) {
    }

    /**
     * @Given /^(the payment method)'s debug mode is (enabled|disabled)/
     */
    public function thePaymentMethodsDebugModeIs(PaymentMethodInterface $paymentMethod, string $debugMode): void
    {
        $debugModeEnabled = $debugMode === 'enabled';

        $this->updatePage->open(['id' => $paymentMethod->getId()]);

        if ($debugModeEnabled) {
            $this->updatePage->enableDebugMode();
        } else {
            $this->updatePage->disableDebugMode();
        }

        $this->updatePage->saveChanges();
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
