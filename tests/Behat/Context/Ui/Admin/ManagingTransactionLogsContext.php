<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\TransactionLog\IndexPageInterface;
use Webmozart\Assert\Assert;

final class ManagingTransactionLogsContext implements Context
{
    public function __construct(
        private IndexPageInterface $indexPage,
    ) {
    }

    /**
     * @When I check the Saferpay's Transaction Logs
     */
    public function iCheckTheSaferpaySTransactionLogs(): void
    {
        $this->indexPage->open();
    }

    /**
     * @Then I should see a single transaction log with type :logType and description :logDescription for order :orderNumber
     * @Then I should see another transaction log with type :logType and description :logDescription for order :orderNumber
     */
    public function iShouldSeeTransactionLogWithTypeAndDescriptionForOrderNumber(string $logType, string $logDescription, string $orderNumber): void
    {
        Assert::true($this->indexPage->hasEntryWithTypeAndDescription($logType, $logDescription, $orderNumber));
    }
}
