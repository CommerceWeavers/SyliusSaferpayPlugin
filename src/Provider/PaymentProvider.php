<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Provider;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PaymentProvider implements PaymentProviderInterface
{
    public function __construct(
        private OrderProviderInterface $orderProvider,
    ) {
    }

    public function provideForAuthorization(string $orderTokenValue): PaymentInterface
    {
        $order = $this->orderProvider->provideForAuthorization($orderTokenValue);

        return $this->provideByOrderAndState($order, PaymentInterface::STATE_NEW);
    }

    public function provideForCapturing(string $orderTokenValue): PaymentInterface
    {
        $order = $this->orderProvider->provideForCapturing($orderTokenValue);

        return $this->provideByOrderAndState($order, PaymentInterface::STATE_AUTHORIZED);
    }

    private function provideByOrderAndState(OrderInterface $order, string $state): PaymentInterface
    {
        $payment = $order->getLastPayment($state);
        if (null === $payment) {
            /** @var string $orderTokenValue */
            $orderTokenValue = $order->getTokenValue();

            throw new NotFoundHttpException(
                sprintf('Order with token "%s" does not have an active payment.', $orderTokenValue),
            );
        }

        return $payment;
    }
}
