<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\AssertFactory;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\AssertFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\PaymentInterface;

final class AssertFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_implements_assert_factory_interface(): void
    {
        self::assertInstanceOf(AssertFactoryInterface::class, new AssertFactory());
    }

    public function test_it_creates_new_assert_request(): void
    {
        $payment = $this->prophesize(PaymentInterface::class)->reveal();

        self::assertEquals(new Assert($payment), (new AssertFactory())->createNewWithModel($payment));
    }
}
