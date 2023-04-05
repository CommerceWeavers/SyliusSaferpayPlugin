<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\PaymentMethod\CreatePageInterface;

final class ManagingPaymentMethodsContext implements Context
{
    public function __construct(
        private CreatePageInterface $createPage,
    ) {
    }

    /**
     * @When I want to create a payment method with Saferpay gateway factory
     */
    public function iWantToCreateANewPaymentMethodWithSaferpayGatewayFactory(): void
    {
        $this->createPage->open(['factory' => 'saferpay']);
    }

    /**
     * @When I configure it with test Saferpay credentials
     */
    public function iConfigureItWithTestSaferpayCredentials(): void
    {
        $this->createPage->setSaferpayUsername('TEST');
        $this->createPage->setSaferpayPassword('TEST');
    }
}
