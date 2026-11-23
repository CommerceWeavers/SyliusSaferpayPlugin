<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\RefundFactory;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\RefundFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Refund;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\PaymentInterface;

final class RefundFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_implements_refund_factory_interface(): void
    {
        self::assertInstanceOf(RefundFactoryInterface::class, new RefundFactory());
    }

    public function test_it_creates_new_refund_request(): void
    {
        $payment = $this->prophesize(PaymentInterface::class)->reveal();

        self::assertEquals(new Refund($payment), (new RefundFactory())->createNewWithModel($payment));
    }
}
