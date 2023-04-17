<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Provider;

use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class OrderProviderSpec extends ObjectBehavior
{
    function let(OrderRepositoryInterface $orderRepository): void
    {
        $this->beConstructedWith($orderRepository);
    }

    function it_throws_an_exception_when_order_with_given_token_does_not_exist_for_authorization(
        OrderRepositoryInterface $orderRepository,
    ): void {
        $orderRepository->findOneByTokenValue('TOKEN')->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException('Order with token "TOKEN" does not exist.'))
            ->during('provideForAuthorization', ['TOKEN'])
        ;
    }

    function it_throws_an_exception_when_order_with_given_token_does_not_exist_for_capturing(
        OrderRepositoryInterface $orderRepository,
    ): void {
        $orderRepository->findOneByTokenValue('TOKEN')->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException('Order with token "TOKEN" does not exist.'))
            ->during('provideForCapturing', ['TOKEN'])
        ;
    }

    function it_provides_order_for_authorization(
        OrderRepositoryInterface $orderRepository,
        OrderInterface $order,
    ): void {
        $orderRepository->findOneByTokenValue('TOKEN')->willReturn($order);

        $this->provideForAuthorization('TOKEN')->shouldReturn($order);
    }

    function it_provides_order_for_capturing(
        OrderRepositoryInterface $orderRepository,
        OrderInterface $order,
    ): void {
        $orderRepository->findOneByTokenValue('TOKEN')->willReturn($order);

        $this->provideForCapturing('TOKEN')->shouldReturn($order);
    }
}
