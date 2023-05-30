<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Mink\Session;
use Doctrine\Persistence\ObjectManager;
use Payum\Core\Payum;
use Payum\Core\Request\Authorize;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Webmozart\Assert\Assert;

final class PaymentContext implements Context
{
    private const SAFERPAY = 'saferpay';

    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private ExampleFactoryInterface $orderExampleFactory,
        private ExampleFactoryInterface $paymentMethodExampleFactory,
        private RepositoryInterface $countryRepository,
        private PaymentMethodRepositoryInterface $paymentMethodRepository,
        private StateMachineFactoryInterface $stateMachineFactory,
        private ObjectManager $objectManager,
        private ObjectManager $orderManager,
        private Payum $payum,
        private RouterInterface $router,
        private Session $session,
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
     * @Given I placed an order with using Saferpay
     */
    public function iPlacedAnOrderWithUsingSaferpay(): void
    {
        /** @var ShopUserInterface $user */
        $user = $this->sharedStorage->get('user');

        $country = $this->countryRepository->findOneBy(['code' => 'US']);

        /** @var OrderInterface $order */
        $order = $this->orderExampleFactory->create([
            'channel' => $this->sharedStorage->get('channel'),
            'customer' => $user->getCustomer(),
            'country' => $country,
            'complete_date' => new \DateTime(),
        ]);

        $this->sharedStorage->set('order', $order);
        $this->orderManager->persist($order);
        $this->orderManager->flush();

        $setOrderIdRoute = $this->router->generate('commerce_weavers_sylius_saferpay_set_order_id', ['orderId' => $order->getId()]);
        $this->session->visit($setOrderIdRoute);
    }

    /**
     * @Given I paid for the order successfully
     */
    public function iPaidForTheOrderSuccessfully(): void
    {
        $authorizeToken = $this->payum->getTokenFactory()->createAuthorizeToken(
            self::SAFERPAY,
            $this->sharedStorage->get('order')->getPayments()->first(),
            'sylius_shop_order_thank_you',
        );

        $authorize = new Authorize($authorizeToken);
        $this->payum->getGateway('saferpay')->execute($authorize);
    }

    /**
     * @Given I did not return to the store
     */
    public function iDidNotReturnToTheStore(): void
    {
        // Intentionally left blank
    }

    /**
     * @Given I returned to the store
     */
    public function iReturnedToTheStore(): void
    {
        /** @var OrderInterface $order */
        $order = $this->sharedStorage->get('order');

        foreach ($order->getPayments() as $payment) {
            $stateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);
            if ($stateMachine->can(PaymentTransitions::TRANSITION_COMPLETE)) {
                $stateMachine->apply(PaymentTransitions::TRANSITION_COMPLETE);
            }
        }

        $this->orderManager->flush();

        $this->sharedStorage->set('order', $order);
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

    private function createSaferpayPaymentMethod(
        array $gatewayConfig = [],
    ): void {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodExampleFactory->create([
            'name' => 'Saferpay',
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
