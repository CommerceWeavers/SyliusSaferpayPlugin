<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentCaptureSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Entity\TransactionLogInterface;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\Exception\PaymentNotFoundException;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Factory\TransactionLogFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Resolver\DebugModeResolverInterface;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Sylius\Calendar\Provider\DateTimeProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;

final class PaymentCaptureSuccessListenerSpec extends ObjectBehavior
{
    function let(
        TransactionLogFactoryInterface $transactionLogFactory,
        ObjectManager $transactionLogManager,
        PaymentRepositoryInterface $paymentRepository,
        DateTimeProviderInterface $dateTimeProvider,
        DebugModeResolverInterface $debugModeResolver,
    ): void {
        $this->beConstructedWith(
            $transactionLogFactory,
            $transactionLogManager,
            $paymentRepository,
            $dateTimeProvider,
            $debugModeResolver,
        );
    }

    function it_does_not_persist_a_transaction_log_when_debug_mode_disabled(
        TransactionLogFactoryInterface $transactionLogFactory,
        PaymentRepositoryInterface $paymentRepository,
        PaymentInterface $payment,
        DateTimeProviderInterface $dateTimeProvider,
        DebugModeResolverInterface $debugModeResolver,
    ): void {
        $now = new \DateTimeImmutable('now');
        $dateTimeProvider->now()->willReturn($now);

        $paymentRepository->find(1)->willReturn($payment);

        $debugModeResolver->isEnabled($payment)->willReturn(false);

        $transactionLogFactory->createInformationalLog()->shouldNotBeCalled();
    }

    function it_persists_a_transaction_log(
        TransactionLogFactoryInterface $transactionLogFactory,
        ObjectManager $transactionLogManager,
        PaymentRepositoryInterface $paymentRepository,
        PaymentInterface $payment,
        TransactionLogInterface $transactionLog,
        DateTimeProviderInterface $dateTimeProvider,
        DebugModeResolverInterface $debugModeResolver,
    ): void {
        $now = new \DateTimeImmutable('now');
        $dateTimeProvider->now()->willReturn($now);

        $paymentRepository->find(1)->willReturn($payment);

        $debugModeResolver->isEnabled($payment)->willReturn(true);

        $paymentCaptureSucceeded = new PaymentCaptureSucceeded(
            1,
            '/saferpay/some/endpoint',
            $this->getExampleRequest(),
            $this->getExampleSuccessData(),
        );

        $transactionLogFactory->createInformationalLog(
            $now,
            $payment,
            'Payment capture succeeded',
            [
                'url' => $paymentCaptureSucceeded->getRequestUrl(),
                'request' => $paymentCaptureSucceeded->getRequestBody(),
                'response' => $paymentCaptureSucceeded->getResponseData(),
            ],
        )->willReturn($transactionLog);

        $transactionLogManager->persist($transactionLog)->shouldBeCalled();
        $transactionLogManager->flush()->shouldBeCalled();

        $this($paymentCaptureSucceeded);
    }

    function it_throws_exception_once_payment_not_found(
        PaymentRepositoryInterface $paymentRepository,
    ): void {
        $paymentCaptureSucceeded = new PaymentCaptureSucceeded(
            1,
            '/saferpay/some/endpoint',
            ['Token' => 'def456'],
            $this->getExampleSuccessData(),
        );

        $paymentRepository->find(1)->willReturn(null);

        $this->shouldThrow(PaymentNotFoundException::class)->during('__invoke', [$paymentCaptureSucceeded]);
    }

    private function getExampleRequest(): array
    {
        return [
            'TransactionReference' => [
                'TransactionId' => 'txn123'
            ]
        ];
    }

    private function getExampleSuccessData(): array
    {
        return [
            'StatusCode' => 200,
            'ResponseHeader' => [
                'SpecVersion' => '1.0.0',
                'RequestId' => 'abc123'
            ],
            'CaptureId' => 'capture123',
            'Status' => 'SUCCESS',
            'Date' => '2023-05-06T12:34:56Z'
        ];
    }
}
