<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\ResolveNextCommandFactory;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\ResolveNextCommandFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\ResolveNextCommand;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\PaymentInterface;

final class ResolveNextCommandFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_implements_resolve_next_command_factory_interface(): void
    {
        self::assertInstanceOf(ResolveNextCommandFactoryInterface::class, new ResolveNextCommandFactory());
    }

    public function test_it_creates_new_resolve_next_command_request(): void
    {
        $payment = $this->prophesize(PaymentInterface::class)->reveal();

        self::assertEquals(new ResolveNextCommand($payment), (new ResolveNextCommandFactory())->createNewWithModel($payment));
    }
}
