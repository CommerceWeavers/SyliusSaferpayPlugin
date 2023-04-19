<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Sylius\Behat\Page\Shop\Checkout\CompletePageInterface;
use Sylius\Behat\Page\Shop\Order\ShowPageInterface;
use Webmozart\Assert\Assert;

final class PaymentContext implements Context
{
    public function __construct(
        private CompletePageInterface $completePage,
        private ShowPageInterface $orderDetails,
        private string $projectDirectory,
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
     * @When I fail to finalize the payment on the Saferpay's page
     */
    public function iFailToFinalizeThePaymentOnTheSaferpaysPage(): void
    {
        file_put_contents($this->projectDirectory . '/var/temporaryToken.txt', 'FAILURE_TOKEN');

        $this->completePage->confirmOrder();
    }

    /**
     * @Then I should be notified that my payment has failed
     */
    public function iShouldBeNotifiedThatMyPaymentHasFailed(): void
    {
        Assert::inArray('Payment has failed.', $this->orderDetails->getNotifications());
    }
}
