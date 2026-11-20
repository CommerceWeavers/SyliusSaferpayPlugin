<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\TransactionLog\EventListener;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAuthorizationFailed;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Entity\TransactionLogInterface;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\Exception\PaymentNotFoundException;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\PaymentAuthorizationFailureListener;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Factory\TransactionLogFactoryInterface;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\Clock\MockClock;

final class PaymentAuthorizationFailureListenerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<TransactionLogFactoryInterface> */
    private ObjectProphecy $transactionLogFactory;

    /** @var ObjectProphecy<ObjectManager> */
    private ObjectProphecy $transactionLogManager;

    /** @var ObjectProphecy<PaymentRepositoryInterface> */
    private ObjectProphecy $paymentRepository;

    private MockClock $clock;

    private PaymentAuthorizationFailureListener $listener;

    protected function setUp(): void
    {
        $this->transactionLogFactory = $this->prophesize(TransactionLogFactoryInterface::class);
        $this->transactionLogManager = $this->prophesize(ObjectManager::class);
        $this->paymentRepository = $this->prophesize(PaymentRepositoryInterface::class);
        $this->clock = new MockClock();

        $this->listener = new PaymentAuthorizationFailureListener(
            $this->transactionLogFactory->reveal(),
            $this->transactionLogManager->reveal(),
            $this->paymentRepository->reveal(),
            $this->clock,
        );
    }

    public function test_it_should_persist_a_transaction_log(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $transactionLog = $this->prophesize(TransactionLogInterface::class);

        $this->paymentRepository->find(1)->willReturn($payment->reveal());

        $event = new PaymentAuthorizationFailed(
            1,
            '/saferpay/some/endpoint',
            ['Token' => 'def456'],
            $this->getExampleFailureData(),
        );

        $this->transactionLogFactory->createErrorLog(
            $this->clock->now(),
            $payment->reveal(),
            'Payment authorization failed',
            [
                'url' => $event->getRequestUrl(),
                'request' => $event->getRequestBody(),
                'response' => $event->getResponseData(),
            ],
        )->willReturn($transactionLog->reveal());

        $this->transactionLogManager->persist($transactionLog->reveal())->shouldBeCalled();
        $this->transactionLogManager->flush()->shouldBeCalled();

        ($this->listener)($event);
    }

    public function test_it_throws_exception_once_payment_not_found(): void
    {
        $event = new PaymentAuthorizationFailed(
            1,
            '/saferpay/some/endpoint',
            ['Token' => 'def456'],
            $this->getExampleFailureData(),
        );

        $this->paymentRepository->find(1)->willReturn(null);

        $this->expectException(PaymentNotFoundException::class);

        ($this->listener)($event);
    }

    private function getExampleFailureData(): array
    {
        return [
            'StatusCode' => 200,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'abc123',
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
                'ProcessorMessage' => null,
            ],
        ];
    }
}
