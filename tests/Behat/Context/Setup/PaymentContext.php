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
    ) {
    }

    /**
     * @Given the store allows paying with Saferpay gateway
     */
    public function theStoreAllowsPayingWithSaferpayGateway(): void
    {
        $this->createSaferpayPaymentMethod(
            [
                'username' => 'test',
                'password' => 'test',
                'customer_id' => '123',
                'terminal_id' => '456',
                'sandbox' => true,
                'use_authorize' => true,
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
}
