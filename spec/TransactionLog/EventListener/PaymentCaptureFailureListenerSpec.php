<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentCaptureFailed;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Entity\TransactionLogInterface;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\Exception\PaymentNotFoundException;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Factory\TransactionLogFactoryInterface;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Sylius\Calendar\Provider\DateTimeProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;

final class PaymentCaptureFailureListenerSpec extends ObjectBehavior
{
    function let(
        TransactionLogFactoryInterface $transactionLogFactory,
        ObjectManager $transactionLogManager,
        PaymentRepositoryInterface $paymentRepository,
        DateTimeProviderInterface $dateTimeProvider,
    ): void {
        $this->beConstructedWith($transactionLogFactory, $transactionLogManager, $paymentRepository, $dateTimeProvider);
    }

    function it_should_persist_a_transaction_log(
        TransactionLogFactoryInterface $transactionLogFactory,
        ObjectManager $transactionLogManager,
        PaymentRepositoryInterface $paymentRepository,
        PaymentInterface $payment,
        TransactionLogInterface $transactionLog,
        DateTimeProviderInterface $dateTimeProvider,
    ): void {
        $now = new \DateTimeImmutable('now');
        $dateTimeProvider->now()->willReturn($now);

        $paymentRepository->find(1)->willReturn($payment);

        $paymentCaptureFailed = new PaymentCaptureFailed(
            1,
            '/saferpay/some/endpoint',
            ['Token' => 'def456'],
            $this->getExampleFailureData(),
        );

        $transactionLogFactory->createErrorLog(
            $now,
            $payment,
            'Payment capture failed',
            [
                'url' => $paymentCaptureFailed->getRequestUrl(),
                'request' => $paymentCaptureFailed->getRequestBody(),
                'response' => $paymentCaptureFailed->getResponseData(),
            ],
        )->willReturn($transactionLog);

        $transactionLogManager->persist($transactionLog)->shouldBeCalled();
        $transactionLogManager->flush()->shouldBeCalled();

        $this($paymentCaptureFailed);
    }

    function it_throws_exception_once_payment_not_found(
        PaymentRepositoryInterface $paymentRepository,
    ): void {
        $paymentCaptureFailed = new PaymentCaptureFailed(
            1,
            '/saferpay/some/endpoint',
            ['Token' => 'def456'],
            $this->getExampleFailureData(),
        );

        $paymentRepository->find(1)->willReturn(null);

        $this->shouldThrow(PaymentNotFoundException::class)->during('__invoke', [$paymentCaptureFailed]);
    }

    private function getExampleFailureData(): array
    {
        return [
            'StatusCode' => 200,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'abc123'
            ],
            'Transaction' => null,
            'PaymentMeans' => null,
            'Liability' => null,
            'Error' => [
                'Name' => 'ExampleError',
                'Message' => 'An example error message',
                'Behavior' => 'BLOCK',
                'TransactionId' => 'txn123',
                'OrderId' => 'order123',
                'PayerMessage' => null,
                'ProcessorName' => null,
                'ProcessorResult' => null,
                'ProcessorMessage' => null
            ]
        ];
    }
}
