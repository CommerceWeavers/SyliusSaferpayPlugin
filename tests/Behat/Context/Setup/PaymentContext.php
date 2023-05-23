<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\Persistence\ObjectManager;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Webmozart\Assert\Assert;

final class PaymentContext implements Context
{
    private const SAFERPAY = 'saferpay';

    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private ExampleFactoryInterface $paymentMethodExampleFactory,
        private PaymentMethodRepositoryInterface $paymentMethodRepository,
        private StateMachineFactoryInterface $stateMachineFactory,
        private ObjectManager $objectManager,
        private array $gatewayFactories,
    ) {
    }

    /**
     * @Given the store allows paying with "Cash on Delivery"
     */
    public function storeAllowsPayingOffline(): void
    {
        $this->createCashOnDeliveryPaymentMethod('PM_' . StringInflector::nameToCode('Cash on Delivery'), 'Payment method');
    }

    /**
     * @Given the store allows paying with Saferpay
     */
    public function theStoreAllowsPayingWithSaferpay(): void
    {
        $this->createSaferpayPaymentMethod(
            [
                'username' => 'test',
                'password' => 'test',
                'customer_id' => '123',
                'terminal_id' => '456',
                'sandbox' => true,
                'use_authorize' => true,
                'allowed_payment_methods' => ['VISA', 'MASTERCARD'],
            ],
        );
    }

    /**
     * @Given /^(this order) is already paid with Saferpay payment$/
     */
    public function thisOrderIsAlreadyPaidWithSaferpayPayment(OrderInterface $order): void
    {
        $payment = $order->getLastPayment(PaymentInterface::STATE_NEW);
        Assert::notNull($payment);

        $this->stateMachineFactory
            ->get($payment, PaymentTransitions::GRAPH)
            ->apply(PaymentTransitions::TRANSITION_COMPLETE)
        ;
        $payment->setDetails(['capture_id' => '1234567890']);

        $this->objectManager->flush();
    }

    /**
     * @Then /^the (latest order) should have a payment with state "([^"]+)"$/
     */
    public function theLatestOrderHasAuthorizedPayment(OrderInterface $order, string $state): void
    {
        /** @var PaymentInterface $payment */
        $payment = $order->getLastPayment();

        Assert::eq($payment->getState(), $state);
    }

    private function createSaferpayPaymentMethod(
        array $gatewayConfig = [],
    ): void {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodExampleFactory->create([
            'name' => ucfirst('Saferpay'),
            'code' => 'saferpay',
            'description' => '',
            'gatewayName' => self::SAFERPAY,
            'gatewayFactory' => StringInflector::nameToLowercaseCode(self::SAFERPAY),
            'gatewayConfig' => $gatewayConfig,
            'enabled' => true,
            'channels' => ($this->sharedStorage->has('channel')) ? [$this->sharedStorage->get('channel')] : [],
        ]);

        $paymentMethod->setPosition(0);

        $this->sharedStorage->set('payment_method', $paymentMethod);
        $this->paymentMethodRepository->add($paymentMethod);
    }

    private function createCashOnDeliveryPaymentMethod(
        string $code,
        string $description = '',
    ): void {
        $gatewayFactory = array_search('Offline', $this->gatewayFactories, true);

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodExampleFactory->create([
            'name' => ucfirst('Cash on Delivery'),
            'code' => $code,
            'description' => $description,
            'gatewayName' => $gatewayFactory,
            'gatewayFactory' => $gatewayFactory,
            'enabled' => true,
            'channels' => $this->sharedStorage->has('channel') ? [$this->sharedStorage->get('channel')] : [],
        ]);

        $this->sharedStorage->set('payment_method', $paymentMethod);
        $this->paymentMethodRepository->add($paymentMethod);
    }
}
