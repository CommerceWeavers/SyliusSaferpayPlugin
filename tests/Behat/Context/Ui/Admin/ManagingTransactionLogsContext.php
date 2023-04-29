<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Event\SaferpayPaymentEvent;
use Sylius\Behat\Page\Admin\Crud\IndexPageInterface;

final class ManagingTransactionLogsContext implements Context
{
    public function __construct(
        private IndexPageInterface $indexPage,
    ) {
    }

    /**
     * @When I check the Saferpay's transaction logs
     */
    public function iCheckTheSaferpaySTransactionLogs(): void
    {
        $this->indexPage->open();
    }

    /**
     * @Then I should see the :logType transaction log for order :orderNumber, described as :logDescription
     */
    public function iShouldSeeTransactionLogWithTypeAndDescriptionForOrderNumber(string $logType, string $logDescription, string $orderNumber): void
    {
        $logType = match ($logType) {
            'successful' => SaferpayPaymentEvent::TYPE_SUCCESS,
            'failed' => SaferpayPaymentEvent::TYPE_ERROR,
            default => throw new \InvalidArgumentException(sprintf('Unknown log type "%s"', $logType)),
        };

        $this->indexPage->isSingleResourceOnPage([
            'type' => $logType,
            'description' => $logDescription,
            'orderNumber' => $orderNumber,
        ]);
    }
}
