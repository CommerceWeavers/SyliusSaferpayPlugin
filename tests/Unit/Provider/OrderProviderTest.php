<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Provider;

use CommerceWeavers\SyliusSaferpayPlugin\Provider\OrderProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class OrderProviderTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<OrderRepositoryInterface> */
    private ObjectProphecy $orderRepository;

    private OrderProvider $orderProvider;

    protected function setUp(): void
    {
        $this->orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        $this->orderProvider = new OrderProvider($this->orderRepository->reveal());
    }

    public function test_it_throws_an_exception_when_order_with_given_token_does_not_exist_for_assert(): void
    {
        $this->orderRepository->findOneByTokenValue('TOKEN')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Order with token "TOKEN" does not exist.');

        $this->orderProvider->provideForAssert('TOKEN');
    }

    public function test_it_throws_an_exception_when_order_with_given_token_does_not_exist_for_capture(): void
    {
        $this->orderRepository->findOneByTokenValue('TOKEN')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Order with token "TOKEN" does not exist.');

        $this->orderProvider->provideForCapture('TOKEN');
    }

    public function test_it_provides_order_for_assert(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $this->orderRepository->findOneByTokenValue('TOKEN')->willReturn($order->reveal());

        $this->assertSame($order->reveal(), $this->orderProvider->provideForAssert('TOKEN'));
    }

    public function test_it_provides_order_for_capture(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $this->orderRepository->findOneByTokenValue('TOKEN')->willReturn($order->reveal());

        $this->assertSame($order->reveal(), $this->orderProvider->provideForCapture('TOKEN'));
    }
}
