<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Processor;

use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentAlreadyProcessedException;
use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentBeingProcessedException;
use CommerceWeavers\SyliusSaferpayPlugin\Processor\SaferpayPaymentProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;

final class SaferpayPaymentProcessorTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $lockFactory;

    private ObjectProphecy $entityManager;

    private ObjectProphecy $logger;

    private SaferpayPaymentProcessor $processor;

    protected function setUp(): void
    {
        $this->lockFactory = $this->prophesize(LockFactory::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->processor = new SaferpayPaymentProcessor(
            $this->lockFactory->reveal(),
            $this->entityManager->reveal(),
            $this->logger->reveal(),
        );
    }

    public function test_it_sets_processing_as_true_on_payment(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $lock = $this->prophesize(SharedLockInterface::class);

        $this->lockFactory->createLock('cw_saferpay_payment_processing_1')->willReturn($lock);
        $lock->acquire()->willReturn(true);

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['status' => 'NEW']);

        $payment->setDetails(['status' => 'NEW', 'processing' => true])->shouldBeCalled();

        $this->entityManager->flush()->shouldBeCalled();
        $lock->release()->shouldBeCalled();

        $this->processor->lock($payment->reveal());
    }

    public function test_it_throws_an_exception_if_status_is_not_set(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $lock = $this->prophesize(SharedLockInterface::class);

        $this->lockFactory->createLock('cw_saferpay_payment_processing_1')->willReturn($lock);
        $lock->acquire()->willReturn(true);

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn([]);

        $payment->setDetails(Argument::any())->shouldNotBeCalled();
        $this->entityManager->flush()->shouldNotBeCalled();
        $lock->release()->shouldBeCalled();

        $this->expectException(PaymentAlreadyProcessedException::class);

        $this->processor->lock($payment->reveal());
    }

    public function test_it_throws_an_exception_if_payment_is_processing(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $lock = $this->prophesize(SharedLockInterface::class);

        $this->lockFactory->createLock('cw_saferpay_payment_processing_1')->willReturn($lock);
        $lock->acquire()->willReturn(true);

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['status' => 'NEW', 'processing' => true]);

        $payment->setDetails(['status' => 'NEW', 'processing' => true])->shouldNotBeCalled();
        $this->entityManager->flush()->shouldNotBeCalled();
        $lock->release()->shouldBeCalled();

        $this->expectException(PaymentBeingProcessedException::class);

        $this->processor->lock($payment->reveal());
    }

    public function test_it_throws_an_exception_if_lock_cannot_be_acquired(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $lock = $this->prophesize(SharedLockInterface::class);

        $this->lockFactory->createLock('cw_saferpay_payment_processing_1')->willReturn($lock);
        $lock->acquire()->willReturn(false);

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['status' => 'NEW']);

        $payment->setDetails(Argument::any())->shouldNotBeCalled();
        $this->entityManager->flush()->shouldNotBeCalled();
        $lock->release()->shouldNotBeCalled();

        $this->expectException(PaymentBeingProcessedException::class);

        $this->processor->lock($payment->reveal());
    }

    public function test_it_throws_an_exception_if_acquiring_lock_throws_exception(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $lock = $this->prophesize(SharedLockInterface::class);

        $this->lockFactory->createLock('cw_saferpay_payment_processing_1')->willReturn($lock);
        $lock->acquire()->willThrow(LockAcquiringException::class);

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['status' => 'NEW']);

        $payment->setDetails(Argument::any())->shouldNotBeCalled();
        $this->entityManager->flush()->shouldNotBeCalled();
        $lock->release()->shouldNotBeCalled();

        $this->expectException(PaymentBeingProcessedException::class);

        $this->processor->lock($payment->reveal());
    }
}
