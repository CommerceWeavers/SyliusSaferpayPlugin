<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Sylius\Behat\Page\Shop\Checkout\CompletePageInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;

final class PaymentContext implements Context
{
    public const SAFERPAY = 'saferpay';

    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private ExampleFactoryInterface $paymentMethodExampleFactory,
        private PaymentMethodRepositoryInterface $paymentMethodRepository,
        private CompletePageInterface $completePage,
    ) {
    }

    /**
     * @Given the store has a payment method :name with a code :code and Saferpay gateway
     */
    public function theStoreHasPaymentMethodWithCodeAndSaferpayGateway(string $name, string $code): void
    {
        $paymentMethod = $this->createPaymentMethod($name, $code);
        $paymentMethod->getGatewayConfig()->setConfig([
            'username' => 'test',
            'password' => 'test',
            'customer_id' => '123',
            'terminal_id' => '456',
            'sandbox' => true,
            'use_authorize' => true,
        ]);

        $this->paymentMethodRepository->add($paymentMethod);
    }

    private function createPaymentMethod(
        string $name,
        string $code,
    ): PaymentMethodInterface {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodExampleFactory->create([
            'name' => ucfirst($name),
            'code' => $code,
            'description' => '',
            'gatewayName' => ucfirst(self::SAFERPAY),
            'gatewayFactory' => self::SAFERPAY,
            'enabled' => true,
            'channels' => ($this->sharedStorage->has('channel')) ? [$this->sharedStorage->get('channel')] : [],
        ]);

        $paymentMethod->setPosition(0);

        $this->sharedStorage->set('payment_method', $paymentMethod);

        return $paymentMethod;
    }

    /**
     * @When I finalize successfully the payment on the Saferpay's page
     */
    public function iFinalizeThePaymentOnTheSaferpayPage(): void
    {
        $this->completePage->confirmOrder();
    }
}
