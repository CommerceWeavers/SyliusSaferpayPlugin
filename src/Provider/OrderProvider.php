<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Provider;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class OrderProvider implements OrderProviderInterface
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function provide(string $tokenValue): OrderInterface
    {
        $order = $this->provideByTokenValue($tokenValue);

        return $order;
    }

    public function provideForAssert(string $tokenValue): OrderInterface
    {
        return $this->provideByTokenValue($tokenValue);
    }

    public function provideForCapture(string $tokenValue): OrderInterface
    {
        return $this->provideByTokenValue($tokenValue);
    }

    private function provideByTokenValue(string $tokenValue): OrderInterface
    {
        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneByTokenValue($tokenValue);
        if (null === $order) {
            throw new NotFoundHttpException(sprintf('Order with token "%s" does not exist.', $tokenValue));
        }

        return $order;
    }
}
