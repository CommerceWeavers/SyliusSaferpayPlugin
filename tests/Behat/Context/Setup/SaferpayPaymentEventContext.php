<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAssertionSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAuthorizationSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentCaptureSucceeded;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class SaferpayPaymentEventContext implements Context
{
    public function __construct(
        private MessageBusInterface $commandBus,
    ) {
    }

    /**
     * @Given /^(this order) has been paid successfully with Saferpay payment method$/
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
