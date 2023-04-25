<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Setup;


use Behat\Behat\Context\Context;
use CommerceWeavers\SyliusSaferpayPlugin\Event\SaferpayPaymentEvent;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class SaferpayPaymentEventContext implements Context
{
    public function __construct (
        private MessageBusInterface $commandBus,
    ) {
    }

    /**
     * @Given /^the system has been notified about payment on (this order)$/
     */
    public function theSystemHasBeenNotifiedAboutPaymentOnThisOrder(OrderInterface $order): void
    {
        $this->commandBus->dispatch(new SaferpayPaymentEvent(
            new \DateTimeImmutable(),
            $order->getPayments()->first()->getId(),
            'Payment authorization',
            [],
        ));

        $this->commandBus->dispatch(new SaferpayPaymentEvent(
            new \DateTimeImmutable(),
            $order->getPayments()->first()->getId(),
            'Payment assertion',
            [],
        ));

        $this->commandBus->dispatch(new SaferpayPaymentEvent(
            new \DateTimeImmutable(),
            $order->getPayments()->first()->getId(),
            'Payment capture',
            [],
        ));
    }
}
