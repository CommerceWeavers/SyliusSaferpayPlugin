<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Event\Handler;

use CommerceWeavers\SyliusSaferpayPlugin\Entity\TransactionLogInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Event\SaferpayPaymentEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\InvalidArgumentException;

final class SaferpayPaymentEventHandlerSpec extends ObjectBehavior
{
    function let(
        FactoryInterface $transactionLogFactory,
        RepositoryInterface $transactionLogRepository,
        PaymentRepositoryInterface $paymentRepository,
    ): void {
        $this->beConstructedWith($transactionLogFactory, $transactionLogRepository, $paymentRepository);
    }

    function it_adds_a_transaction_log(
        FactoryInterface $transactionLogFactory,
        RepositoryInterface $transactionLogRepository,
        PaymentRepositoryInterface $paymentRepository,
        PaymentInterface $payment,
        TransactionLogInterface $transactionLog,
    ): void {
        $paymentRepository->find(1)->willReturn($payment->getWrappedObject());
        $transactionLogFactory->createNew()->willReturn($transactionLog);

        $transactionLog->setCreatedAt(Argument::type(\DateTimeInterface::class))->shouldBeCalled();
        $transactionLog->setPayment($payment)->shouldBeCalled();
        $transactionLog->setStatus('status')->shouldBeCalled();
        $transactionLog->setDescription('description')->shouldBeCalled();
        $transactionLog->setContext(['context'])->shouldBeCalled();

        $transactionLogRepository->add($transactionLog)->shouldBeCalled();

        $this(new SaferpayPaymentEvent(
            1,
            'status',
            'description',
            ['context'],
        ));
    }

    function it_throws_an_exception_when_payment_with_id_passed_in_event_is_not_found(
        PaymentRepositoryInterface $paymentRepository,
    ): void {
        $paymentRepository->find(1)->willReturn(null);

        $this->shouldThrow(InvalidArgumentException::class)->during('__invoke', [
            new SaferpayPaymentEvent(
                1,
                'status',
                'description',
                ['context'],
            ),
        ]);
    }
}
