<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\PaymentMethod\ConfigurePaymentMethodsPageInterface;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\PaymentMethod\CreatePageInterface;
use Webmozart\Assert\Assert;

final class ManagingPaymentMethodsContext implements Context
{
    public function __construct(
        private CreatePageInterface $createPage,
        private ConfigurePaymentMethodsPageInterface $configurePaymentMethodsPage,
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
     * @When I configure it with provided Saferpay credentials
     */
    public function iConfigureItWithProvidedSaferpayCredentials(): void
    {
        $this->createPage->setSaferpayUsername('TEST');
        $this->createPage->setSaferpayPassword('TEST');
        $this->createPage->setSaferpayCustomerId('TEST');
        $this->createPage->setSaferpayTerminalId('TEST');
    }

    /**
     * @When I want to configure the available payment methods for :paymentMethod gateway
     */
    public function iWantToConfigureTheAvailablePaymentMethodsForSaferpayGateway(PaymentMethodInterface $paymentMethod): void
    {
        $this->configurePaymentMethodsPage->open(['id' => $paymentMethod->getId()]);
    }

    /**
     * @When I disable the :firstPaymentMethodName and :secondPaymentMethodName payment methods
     */
    public function iDisableThePaymentMethods(string ...$paymentMethodNames): void
    {
        foreach ($paymentMethodNames as $paymentMethodName) {
            $this->configurePaymentMethodsPage->disablePaymentMethod($paymentMethodName);
        }
    }

    /**
     * @When I save the configuration
     */
    public function iSaveTheConfiguration(): void
    {
        $this->configurePaymentMethodsPage->saveChanges();
    }

    /**
     * @Then I should see that all payment methods are available
     */
    public function iShouldSeeThatAllPaymentMethodsAreAvailable(): void
    {
        Assert::true($this->configurePaymentMethodsPage->isAllPaymentMethodsEnabled());
    }

    /**
     * @Then the :firstPaymentMethodName and :secondPaymentMethodName payment methods should be unavailable
     */
    public function thePaymentMethodsShouldBeUnavailable(string ...$paymentMethodNames): void
    {
        foreach ($paymentMethodNames as $paymentMethodName) {
            Assert::false($this->configurePaymentMethodsPage->isPaymentMethodEnabled($paymentMethodName));
        }
    }
}
