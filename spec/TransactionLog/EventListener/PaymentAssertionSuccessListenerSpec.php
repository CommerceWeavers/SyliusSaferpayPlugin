<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener;

use CommerceWeavers\SyliusSaferpayPlugin\Entity\TransactionLogInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Factory\TransactionLogFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAssertionSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\Exception\PaymentNotFoundException;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Sylius\Calendar\Provider\DateTimeProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;

final class PaymentAssertionSuccessListenerSpec extends ObjectBehavior
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

        $paymentAssertionSucceeded = new PaymentAssertionSucceeded(
            1,
            '/saferpay/some/endpoint',
            ['Token' => 'def456'],
            $this->getExampleSuccessData(),
        );

        $transactionLogFactory->create(
            $now,
            $payment,
            'Payment assertion succeeded',
            [
                'url' => $paymentAssertionSucceeded->getRequestUrl(),
                'request' => $paymentAssertionSucceeded->getRequestBody(),
                'response' => $paymentAssertionSucceeded->getResponseData(),
            ],
            'info'
        )->willReturn($transactionLog);

        $transactionLogManager->persist($transactionLog)->shouldBeCalled();
        $transactionLogManager->flush()->shouldBeCalled();

        $this->__invoke($paymentAssertionSucceeded);
    }

    function it_throws_exception_once_payment_not_found(
        PaymentRepositoryInterface $paymentRepository,
    ): void {
        $paymentAssertionSucceeded = new PaymentAssertionSucceeded(
            1,
            '/saferpay/some/endpoint',
            ['Token' => 'def456'],
            $this->getExampleSuccessData(),
        );

        $paymentRepository->find(1)->willReturn(null);

        $this->shouldThrow(PaymentNotFoundException::class)->during('__invoke', [$paymentAssertionSucceeded]);
    }

    private function getExampleSuccessData(): array
    {
        return [
            'StatusCode' => 200,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'abc123'
            ],
            'Transaction' => [
                'Type' => 'PURCHASE',
                'Status' => 'APPROVED',
                'Id' => 'txn123',
                'Date' => '2023-05-06T12:34:56Z',
                'Amount' => [
                    'Value' => 1000,
                    'CurrencyCode' => 'USD'
                ],
                'AcquirerName' => 'ExampleAcquirer',
                'AcquirerReference' => 'ref123',
                'SixTransactionReference' => 'sixref123',
                'ApprovalCode' => 'appr123'
            ],
            'PaymentMeans' => [
                'Brand' => [
                    'PaymentMethod' => 'CARD',
                    'Name' => 'VISA'
                ],
                'DisplayText' => 'Visa **** 1234',
                'Card' => [
                    'MaskedNumber' => '************1234',
                    'ExpYear' => '2025',
                    'ExpMonth' => '12',
                    'HolderName' => 'John Doe',
                    'CountryCode' => 'US'
                ]
            ],
            'Liability' => [
                'LiabilityShift' => true,
                'LiabilityEntity' => 'ACQUIRER',
                'ThreeDs' => [
                    'Authenticated' => true,
                    'LiabilityShift' => true,
                    'Xid' => '3dsxid123'
                ]
            ],
            'Error' => null
        ];
    }
}
