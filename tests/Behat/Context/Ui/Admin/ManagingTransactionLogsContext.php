<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\TransactionLog\IndexPageInterface;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\TransactionLog\ShowPageInterface;
use Webmozart\Assert\Assert;

final class ManagingTransactionLogsContext implements Context
{
    public function __construct(private IndexPageInterface $indexPage, private ShowPageInterface $showPage)
    {
    }

    /**
     * @When I check the Saferpay's transaction logs
     */
    public function iCheckTheSaferpaySTransactionLogs(): void
    {
        $this->indexPage->open();
    }

    /**
     * @Then I should see :count transaction log(s) in the list
     */
    public function iShouldSeeTransactionLogsInTheList(int $count): void
    {
        Assert::same($this->indexPage->countItems(), $count);
    }

    /**
     * @When I open details of the :logDescription log for order :orderNumber
     */
    public function iOpenDetailsOfTheLogForOrder(string $logDescription, string $orderNumber): void
    {
        $this->indexPage->open();
        $this->indexPage->openLogDetailsByOrderNumberAndDescription($orderNumber, $logDescription);
    }

    /**
     * @Then I should see the :logType transaction log for order :orderNumber, described as :logDescription
     */
    public function iShouldSeeTransactionLogWithTypeAndDescriptionForOrderNumber(string $logType, string $logDescription, string $orderNumber): void
    {
        $logType = match ($logType) {
            'informational' => 'info',
            'error' => 'error',
            default => throw new \InvalidArgumentException(sprintf('Unknown log type "%s"', $logType)),
        };

        $this->indexPage->isSingleResourceOnPage([
            'type' => $logType,
            'description' => $logDescription,
            'orderNumber' => $orderNumber,
        ]);
    }

    /**
     * @Then I should see details of the log with :logDescription description and :logType type
     */
    public function iShouldSeeThePaymentAuthorizationSucceededLogDetails(string $logDescription, string $logType): void
    {
        Assert::same($this->showPage->getDescription(), $logDescription);
        Assert::same($this->showPage->getLogType(), $logType);
    }

    /**
     * @Then I should see the context information about communication with Saferpay
     */
    public function iShouldSeeTheContextInformationAboutCommunicationWithSaferpay(): void
    {
        Assert::true($this->showPage->getContext() !== '');
    }
}
