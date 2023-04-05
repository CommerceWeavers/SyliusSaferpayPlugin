<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;

final class PaymentContext implements Context
{
    /**
     * @Given the store has a payment method :name with a code :code and Saferpay gateway
     */
    public function theStoreHasPaymentMethodWithCodeAndSaferpayGateway(string $name, string $code): void
    {
        throw new \Exception('Not implemented yet.');
    }

    /**
     * @When I finalize successfully the payment on the Saferpay's page
     */
    public function iFinalizeThePaymentOnTheSaferpayPage(): void
    {
        throw new \Exception('Not implemented yet.');
    }
}
