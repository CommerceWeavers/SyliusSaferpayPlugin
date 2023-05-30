<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Webhook;

use Behat\Behat\Context\Context;
use Behat\Mink\Session;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use Doctrine\Persistence\ObjectManager;
use Payum\Core\Payum;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class PaymentContext implements Context
{
    public function __construct(
        private Session $session,
        private SharedStorageInterface $sharedStorage,
        private TokenProviderInterface $tokenProvider,
        private ObjectManager $paymentManager,
        private Payum $payum,
    ) {
    }

    /**
     * @When the system receives a notification about payment status
     * @When before I returned to the store, the system received a notification about payment status
     */
    public function theSystemReceivesANotificationAboutPaymentStatus(): void
    {
        /** @var OrderInterface $order */
        $order = $this->sharedStorage->get('order');
        /** @var PaymentInterface $payment */
        $payment = $order->getPayments()->first();
        $webhookToken = $this->tokenProvider->provideForWebhook($payment, 'commerce_weavers_sylius_saferpay_webhook');

        $this->session->visit($webhookToken->getTargetUrl());
    }

    /**
     * @When I return to the store
     */
    public function iReturnToTheStore(): void
    {
        /** @var OrderInterface $order */
        $order = $this->sharedStorage->get('order');

        /** @var PaymentInterface $payment */
        $payment = $order->getPayments()->first();

        $authorizeToken = $this->payum->getTokenFactory()->createAuthorizeToken(
            'saferpay',
            $payment,
            'sylius_shop_order_after_pay',
        );

        $this->session->visit($authorizeToken->getTargetUrl());
    }

    /**
     * @Then /^there should be only one payment for (this order)$/
     */
    public function thereShouldBeOnlyOnePaymentForThisOrder(OrderInterface $order): void
    {
        $payments = $order->getPayments();

        Assert::count($payments, 1);

        $this->sharedStorage->set('payment', $payments->first());
    }

    /**
     * @Then /^(the payment) should be completed$/
     */
    public function thePaymentShouldBeCompleted(PaymentInterface $payment): void
    {
        $this->paymentManager->refresh($payment);

        Assert::same($payment->getState(), PaymentInterface::STATE_COMPLETED);
    }
}
