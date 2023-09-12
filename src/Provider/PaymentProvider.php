<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Provider;

use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentAlreadyProcessedException;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class PaymentProvider implements PaymentProviderInterface
{
    public function __construct(
        private OrderProviderInterface $orderProvider,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function provideForAssert(string $orderTokenValue): PaymentInterface
    {
        $order = $this->orderProvider->provideForAssert($orderTokenValue);

        return $this->provideByOrderAndState($order, PaymentInterface::STATE_NEW);
    }

    public function provideForOrder(string $orderTokenValue): PaymentInterface
    {
        $order = $this->orderProvider->provideForAssert($orderTokenValue);

        $this->entityManager->refresh($order);

        /** @var PaymentInterface $payment */
        $payment = $order->getLastPayment();

        return $payment;
    }

    public function provideForCapture(string $orderTokenValue): PaymentInterface
    {
        $order = $this->orderProvider->provideForCapture($orderTokenValue);

        return $this->provideByOrderAndState($order, PaymentInterface::STATE_AUTHORIZED);
    }

    private function provideByOrderAndState(OrderInterface $order, string $state): PaymentInterface
    {
        $payment = $order->getLastPayment($state);
        if (null === $payment) {
            throw new PaymentAlreadyProcessedException();
        }

        return $payment;
    }
}
