<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Page\Admin\Order\ShowPageInterface;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Operator\TemporaryRequestIdFileOperatorInterface;
use Webmozart\Assert\Assert;

final class ManagingOrdersContext implements Context
{
    public function __construct(
        private ShowPageInterface $showPage,
        private NotificationCheckerInterface $notificationChecker,
        private TemporaryRequestIdFileOperatorInterface $temporaryRequestIdOperator,
    ) {
    }

    /**
     * @When /^I fail to mark (this order)'s payment as refunded$/
     */
    public function iFailToMarkThisOrdersPaymentAsRefunded(OrderInterface $order): void
    {
        $this->temporaryRequestIdOperator->setRequestId('FAILURE_REQUEST_ID');

        $this->showPage->refundOrderLastPayment($order);
    }

    /**
     * @Then I should be notified that the refund has failed
     */
    public function iShouldBeNotifiedThatTheRefundHasFailed(): void
    {
        $this->notificationChecker->checkNotification(
            'Payment refund has failed',
            NotificationType::failure(),
        );
    }

    /**
     * @Then it should still have payment with state :paymentState
     */
    public function itShouldStillHavePaymentState(string $paymentState): void
    {
        Assert::true($this->showPage->hasPayment($paymentState));
    }
}
