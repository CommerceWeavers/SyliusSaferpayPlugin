<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Doctrine\Persistence\ObjectManager;
use Sylius\Behat\Page\Shop\Checkout\CompletePageInterface;
use Sylius\Behat\Page\Shop\Order\ShowPageInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Operator\TemporaryRequestIdFileOperator;
use Webmozart\Assert\Assert;

final class PaymentContext implements Context
{
    public function __construct(
        private CompletePageInterface $completePage,
        private ShowPageInterface $orderDetails,
        private TemporaryRequestIdFileOperator $temporaryRequestIdFileOperator,
        private OrderRepositoryInterface $orderRepository,
        private ObjectManager $orderManager,
    ) {
    }

    /**
     * @When I finalize successfully the payment on the Saferpay's page
     */
    public function iFinalizeSuccessfullyThePaymentOnTheSaferpaysPage(): void
    {
        $this->completePage->confirmOrder();
    }

    /**
     * @Given I have failed to complete the payment on the Saferpay's page
     * @When I fail to complete the payment on the Saferpay's page
     */
    public function iFailToCompleteThePaymentOnTheSaferpaysPage(): void
    {
        $this->temporaryRequestIdFileOperator->setRequestId('FAILURE_REQUEST_ID');

        $this->completePage->confirmOrder();
    }

    /**
     * @Given I have cancelled the payment on the Saferpay's page
     * @When I cancel the payment on the Saferpay's page
     */
    public function iCancelThePaymentOnTheSaferpaysPage(): void
    {
        $this->temporaryRequestIdFileOperator->setRequestId('CANCELLATION_REQUEST_ID');

        $this->completePage->confirmOrder();
    }

    /**
     * @When I fail again to complete the payment on the Saferpay's page
     * @When I cancel again the payment on the Saferpay's page
     */
    public function iTryAgainToCompleteThePaymentOnTheSaferpaysPage(): void
    {
        $this->orderDetails->pay();
    }

    /**
     * @When I successfully pay again on the Saferpay's page
     */
    public function iSuccessfullyPayAgainOnTheSaferpaysPage(): void
    {
        $this->temporaryRequestIdFileOperator->clearRequestId();

        $this->orderDetails->pay();
    }

    /**
     * @Then I should be notified that my payment has failed
     */
    public function iShouldBeNotifiedThatMyPaymentHasFailed(): void
    {
        $this->assertNotificationContains('Payment has failed.');
    }

    /**
     * @Then I should see a successful payment message
     * @Then I should be notified that my payment has been completed
     */
    public function iShouldSeeASuccessfulPaymentMessage(): void
    {
        $this->assertNotificationContains('Payment has been completed.');
    }

    /**
     * @Then I should be notified that my payment has been cancelled
     */
    public function iShouldBeNotifiedThatMyPaymentHasBeenCancelled(): void
    {
        $this->assertNotificationContains('Payment has been cancelled.');
    }

    /**
     * @Then the latest order should have a payment with state :state
     */
    public function theLatestOrderShouldHaveAPaymentWithState(string $state): void
    {
        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findBy(['checkoutState' => OrderCheckoutStates::STATE_COMPLETED], ['id' => 'DESC'], 1)[0] ?? null;
        Assert::notNull($order);

        // the order may still be in the identity map with the state it had before the HTTP requests
        $this->orderManager->refresh($order);

        $payment = $order->getLastPayment();
        Assert::notNull($payment);
        $this->orderManager->refresh($payment);
        Assert::same($payment->getState(), $state);
    }

    private function assertNotificationContains(string $message): void
    {
        $notifications = $this->orderDetails->getNotifications();

        foreach ($notifications as $notification) {
            if (str_contains($notification, $message)) {
                return;
            }
        }

        throw new \InvalidArgumentException(sprintf(
            'Expected a notification containing "%s", got: "%s".',
            $message,
            implode('", "', $notifications),
        ));
    }
}
