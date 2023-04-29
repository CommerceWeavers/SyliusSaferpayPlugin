<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Event\Handler;

use CommerceWeavers\SyliusSaferpayPlugin\Entity\TransactionLogInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Factory\TransactionLogFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Event\Listener\Exception\PaymentNotFound;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Event\SaferpayPaymentEvent;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;

final class SaferpayPaymentEventHandlerSpec extends ObjectBehavior
{
    function let(
        TransactionLogFactoryInterface $transactionLogFactory,
        PaymentRepositoryInterface $paymentRepository,
        ObjectManager $transactionLogManager,
    ): void {
        $this->beConstructedWith($transactionLogFactory, $paymentRepository, $transactionLogManager);
    }

    /** @throws PaymentNotFound */
    function it_adds_a_transaction_log(
        TransactionLogFactoryInterface $transactionLogFactory,
        PaymentRepositoryInterface $paymentRepository,
        PaymentInterface $payment,
        TransactionLogInterface $transactionLog,
        ObjectManager $transactionLogManager,
    ): void {
        $paymentRepository->find(1)->willReturn($payment->getWrappedObject());

        $transactionLogFactory
            ->create(
                Argument::type(\DateTimeInterface::class),
                $payment->getWrappedObject(),
                'description',
                ['context'],
                'type',
            )
            ->willReturn($transactionLog->getWrappedObject())
        ;

        $transactionLogManager->persist($transactionLog)->shouldBeCalled();

        $this(new SaferpayPaymentEvent(
            new \DateTimeImmutable(),
            1,
            'description',
            ['context'],
            'type'
        ));
    }

    function it_throws_an_exception_when_payment_with_id_passed_in_event_is_not_found(
        PaymentRepositoryInterface $paymentRepository,
    ): void {
        $paymentRepository->find(1)->willReturn(null);

        $this->shouldThrow(PaymentNotFound::class)->during('__invoke', [
            new SaferpayPaymentEvent(
                new \DateTimeImmutable(),
                1,
                'description',
                ['context'],
                SaferpayPaymentEvent::TYPE_ERROR,
            ),
        ]);
    }
}
