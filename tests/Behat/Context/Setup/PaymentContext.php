<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;

final class PaymentContext implements Context
{
    public const SAFERPAY = 'saferpay';

    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private ExampleFactoryInterface $paymentMethodExampleFactory,
        private PaymentMethodRepositoryInterface $paymentMethodRepository,
    ) {
    }

    /**
     * @Given the store has a payment method :name with a code :code and Saferpay gateway
     */
    public function theStoreHasPaymentMethodWithCodeAndSaferpayGateway(string $name, string $code): void
    {
        $this->createPaymentMethod(
            $name,
            $code,
            self::SAFERPAY,
            [
                'username' => 'test',
                'password' => 'test',
                'customer_id' => '123',
                'terminal_id' => '456',
                'sandbox' => true,
                'use_authorize' => true,
            ]
        );
    }

    private function createPaymentMethod(
        string $name,
        string $code,
        string $gatewayName = self::SAFERPAY,
        array $gatewayConfig = []
    ): PaymentMethodInterface {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodExampleFactory->create([
            'name' => ucfirst($name),
            'code' => $code,
            'description' => '',
            'gatewayName' => $gatewayName,
            'gatewayFactory' => StringInflector::nameToLowercaseCode($gatewayName),
            'gatewayConfig' => $gatewayConfig,
            'enabled' => true,
            'channels' => ($this->sharedStorage->has('channel')) ? [$this->sharedStorage->get('channel')] : [],
        ]);

        $paymentMethod->setPosition(0);

        $this->sharedStorage->set('payment_method', $paymentMethod);
        $this->paymentMethodRepository->add($paymentMethod);

        return $paymentMethod;
    }
}
