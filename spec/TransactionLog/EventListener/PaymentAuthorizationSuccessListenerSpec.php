<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAuthorizationSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Entity\TransactionLogInterface;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\Exception\PaymentNotFoundException;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Resolver\DebugModeResolverInterface;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Factory\TransactionLogFactoryInterface;
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

        $paymentAuthorizationSucceeded = new PaymentAuthorizationSucceeded(
            1,
            '/saferpay/some/endpoint',
            $this->getExampleRequest(),
            $this->getExampleSuccessData(),
        );

        $transactionLogFactory->createInformationalLog(
            $now,
            $payment,
            'Payment authorization succeeded',
            [
                'url' => $paymentAuthorizationSucceeded->getRequestUrl(),
                'request' => $paymentAuthorizationSucceeded->getRequestBody(),
                'response' => $paymentAuthorizationSucceeded->getResponseData(),
            ],
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
