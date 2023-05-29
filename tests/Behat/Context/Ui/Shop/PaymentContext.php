<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Sylius\Behat\Page\Shop\Checkout\CompletePageInterface;
use Sylius\Behat\Page\Shop\Order\ShowPageInterface;
use Symfony\Component\BrowserKit\HttpBrowser;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Operator\TemporaryRequestIdFileOperator;
use Webmozart\Assert\Assert;

final class PaymentContext implements Context
{
    public function __construct(
        private CompletePageInterface $completePage,
        private ShowPageInterface $orderDetails,
        private TemporaryRequestIdFileOperator $temporaryRequestIdFileOperator,
        private StorageInterface $tokenStorage,
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
     * @When I back to the store
     */
    public function iBackToTheStore(): void
    {
        // Intentionally left blank
    }

    /**
     * @When the system receives a notification about payment status
     */
    public function theSystemReceivesANotificationAboutPaymentStatus(): void
    {
        /** @var TokenInterface $token */
        $token = $this->tokenStorage->findBy([])[0];

        $browser = new HttpBrowser();
        $browser->request('GET', $token->getTargetUrl());
    }

    /**
     * @Then I should be notified that my payment has failed
     */
    public function iShouldBeNotifiedThatMyPaymentHasFailed(): void
    {
        Assert::inArray('Payment has failed.', $this->orderDetails->getNotifications());
    }

    /**
     * @Then I should see a successful payment message
     */
    public function iShouldSeeASuccessfulPaymentMessage(): void
    {
        Assert::inArray('Payment has been completed.', $this->orderDetails->getNotifications());
    }
}
