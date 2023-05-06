<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener;

use CommerceWeavers\SyliusSaferpayPlugin\Client\Event\PaymentAuthorizationSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Entity\TransactionLogInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Factory\TransactionLogFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\Exception\PaymentNotFoundException;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Sylius\Calendar\Provider\DateTimeProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;

final class PaymentAuthorizationSuccessListenerSpec extends ObjectBehavior
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

        $paymentAuthorizationSucceeded = new PaymentAuthorizationSucceeded(
            1,
            '/saferpay/some/endpoint',
            $this->getExampleRequest(),
            $this->getExampleSuccessData(),
        );

        $transactionLogFactory->create(
            $now,
            $payment,
            'Payment authorization succeeded',
            [
                'url' => $paymentAuthorizationSucceeded->getRequestUrl(),
                'request' => $paymentAuthorizationSucceeded->getRequestBody(),
                'response' => $paymentAuthorizationSucceeded->getResponseData(),
            ],
            'info'
        )->willReturn($transactionLog);

        $transactionLogManager->persist($transactionLog)->shouldBeCalled();
        $transactionLogManager->flush()->shouldBeCalled();

        $this->__invoke($paymentAuthorizationSucceeded);
    }

    function it_throws_exception_once_payment_not_found(
        PaymentRepositoryInterface $paymentRepository,
    ): void {
        $paymentAuthorizationSucceeded = new PaymentAuthorizationSucceeded(
            1,
            '/saferpay/some/endpoint',
            ['Token' => 'def456'],
            $this->getExampleSuccessData(),
        );

        $paymentRepository->find(1)->willReturn(null);

        $this->shouldThrow(PaymentNotFoundException::class)->during('__invoke', [$paymentAuthorizationSucceeded]);
    }

    private function getExampleRequest(): array
    {
        return [
            'TerminalId' => 'term123',
            'Payment' => [
                'Amount' => [
                    'Value' => 1000,
                    'CurrencyCode' => 'USD'
                ],
                'OrderId' => 'order123',
                'Description' => 'Example payment description'
            ],
            'ReturnUrl' => [
                'Url' => 'https://example.com/return-url'
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
            'Token' => 'token123',
            'Expiration' => '2023-05-06T13:00:00Z',
            'RedirectUrl' => 'https://example.com/return-url'
        ];
    }
}
