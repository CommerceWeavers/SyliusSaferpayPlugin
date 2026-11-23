<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\CaptureFactory;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\CaptureFactoryInterface;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\PaymentInterface;

final class CaptureFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_implements_capture_factory_interface(): void
    {
        self::assertInstanceOf(CaptureFactoryInterface::class, new CaptureFactory());
    }

    public function test_it_creates_new_capture_request(): void
    {
        $payment = $this->prophesize(PaymentInterface::class)->reveal();

        self::assertEquals(new Capture($payment), (new CaptureFactory())->createNewWithModel($payment));
    }
}
