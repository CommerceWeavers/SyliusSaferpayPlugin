<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Processor;

use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentAlreadyProcessedException;
use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentBeingProcessedException;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

final class SaferpayPaymentProcessorSpec extends ObjectBehavior
{
    function let(
        LockFactory $lockFactory,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
    ): void {
        $this->beConstructedWith($lockFactory, $entityManager, $logger);
    }

    function it_sets_processing_as_true_on_payment(
        LockFactory $lockFactory,
        EntityManagerInterface $entityManager,
        PaymentInterface $payment,
        LockInterface $lock,
    ): void {
        $lockFactory->createLock('payment_processing')->willReturn($lock);
        $lock->acquire()->willReturn(true);

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['status' => 'NEW']);

        $payment->setDetails(['status' => 'NEW', 'processing' => true])->shouldBeCalled();

        $entityManager->flush()->shouldBeCalled();
        $lock->release()->shouldBeCalled();

        $this->lock($payment);
    }

    function it_throws_an_exception_if_status_is_not_set(
        LockFactory $lockFactory,
        EntityManagerInterface $entityManager,
        PaymentInterface $payment,
        LockInterface $lock,
    ): void {
        $lockFactory->createLock('payment_processing')->willReturn($lock);
        $lock->acquire()->willReturn(true);

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn([]);

        $payment->setDetails(Argument::any())->shouldNotBeCalled();
        $entityManager->flush()->shouldNotBeCalled();
        $lock->release()->shouldNotBeCalled();

        $this
            ->shouldThrow(PaymentAlreadyProcessedException::class)
            ->during('lock', [$payment])
        ;
    }

    function it_throws_an_exception_if_payment_is_processing(
        LockFactory $lockFactory,
        EntityManagerInterface $entityManager,
        PaymentInterface $payment,
        LockInterface $lock,
    ): void {
        $lockFactory->createLock('payment_processing')->willReturn($lock);
        $lock->acquire()->willReturn(true);

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['status' => 'NEW', 'processing' => true]);

        $payment->setDetails(['status' => 'NEW', 'processing' => true])->shouldNotBeCalled();
        $entityManager->flush()->shouldNotBeCalled();
        $lock->release()->shouldNotBeCalled();

        $this
            ->shouldThrow(PaymentBeingProcessedException::class)
            ->during('lock', [$payment])
        ;
    }

    function it_throws_an_exception_if_lock_cannot_be_acquired(
        LockFactory $lockFactory,
        EntityManagerInterface $entityManager,
        PaymentInterface $payment,
        LockInterface $lock,
    ): void {
        $lockFactory->createLock('payment_processing')->willReturn($lock);
        $lock->acquire()->willReturn(false);

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['status' => 'NEW']);

        $payment->setDetails(Argument::any())->shouldNotBeCalled();
        $entityManager->flush()->shouldNotBeCalled();
        $lock->release()->shouldNotBeCalled();

        $this
            ->shouldThrow(PaymentBeingProcessedException::class)
            ->during('lock', [$payment])
        ;
    }

    function it_throws_an_exception_if_acquiring_lock_throws_exception(
        LockFactory $lockFactory,
        EntityManagerInterface $entityManager,
        PaymentInterface $payment,
        LockInterface $lock,
    ): void {
        $lockFactory->createLock('payment_processing')->willReturn($lock);
        $lock->acquire()->willThrow(LockAcquiringException::class);

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn(['status' => 'NEW']);

        $payment->setDetails(Argument::any())->shouldNotBeCalled();
        $entityManager->flush()->shouldNotBeCalled();
        $lock->release()->shouldNotBeCalled();

        $this
            ->shouldThrow(PaymentBeingProcessedException::class)
            ->during('lock', [$payment])
        ;
    }
}
