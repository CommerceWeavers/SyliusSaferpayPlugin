<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\TransactionLog\EventListener;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentCaptureSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Entity\TransactionLogInterface;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\Exception\PaymentNotFoundException;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\EventListener\PaymentCaptureSuccessListener;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Factory\TransactionLogFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Resolver\DebugModeResolverInterface;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\Clock\MockClock;

final class PaymentCaptureSuccessListenerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<TransactionLogFactoryInterface> */
    private ObjectProphecy $transactionLogFactory;

    /** @var ObjectProphecy<ObjectManager> */
    private ObjectProphecy $transactionLogManager;

    /** @var ObjectProphecy<PaymentRepositoryInterface> */
    private ObjectProphecy $paymentRepository;

    private MockClock $clock;

    /** @var ObjectProphecy<DebugModeResolverInterface> */
    private ObjectProphecy $debugModeResolver;

    private PaymentCaptureSuccessListener $listener;

    protected function setUp(): void
    {
        $this->transactionLogFactory = $this->prophesize(TransactionLogFactoryInterface::class);
        $this->transactionLogManager = $this->prophesize(ObjectManager::class);
        $this->paymentRepository = $this->prophesize(PaymentRepositoryInterface::class);
        $this->clock = new MockClock();
        $this->debugModeResolver = $this->prophesize(DebugModeResolverInterface::class);

        $this->listener = new PaymentCaptureSuccessListener(
            $this->transactionLogFactory->reveal(),
            $this->transactionLogManager->reveal(),
            $this->paymentRepository->reveal(),
            $this->clock,
            $this->debugModeResolver->reveal(),
        );
    }

    public function test_it_does_not_persist_a_transaction_log_when_debug_mode_disabled(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);

        $this->paymentRepository->find(1)->willReturn($payment->reveal());

        $this->debugModeResolver->isEnabled($payment->reveal())->willReturn(false);

        $this->transactionLogFactory->createInformationalLog(Argument::cetera())->shouldNotBeCalled();
        $this->transactionLogManager->persist(Argument::any())->shouldNotBeCalled();
        $this->transactionLogManager->flush()->shouldNotBeCalled();

        ($this->listener)(new PaymentCaptureSucceeded(
            1,
            '/saferpay/some/endpoint',
            $this->getExampleRequest(),
            $this->getExampleSuccessData(),
        ));
    }

    public function test_it_persists_a_transaction_log(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $transactionLog = $this->prophesize(TransactionLogInterface::class);

        $this->paymentRepository->find(1)->willReturn($payment->reveal());

        $this->debugModeResolver->isEnabled($payment->reveal())->willReturn(true);

        $event = new PaymentCaptureSucceeded(
            1,
            '/saferpay/some/endpoint',
            $this->getExampleRequest(),
            $this->getExampleSuccessData(),
        );

        $this->transactionLogFactory->createInformationalLog(
            $this->clock->now(),
            $payment->reveal(),
            'Payment capture succeeded',
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
        $event = new PaymentCaptureSucceeded(
            1,
            '/saferpay/some/endpoint',
            ['Token' => 'def456'],
            $this->getExampleSuccessData(),
        );

        $this->paymentRepository->find(1)->willReturn(null);

        $this->expectException(PaymentNotFoundException::class);

        ($this->listener)($event);
    }

    private function getExampleRequest(): array
    {
        return [
            'TransactionReference' => [
                'TransactionId' => 'txn123',
            ],
        ];
    }

    private function getExampleSuccessData(): array
    {
        return [
            'StatusCode' => 200,
            'ResponseHeader' => [
                'SpecVersion' => '1.0.0',
                'RequestId' => 'abc123',
            ],
            'CaptureId' => 'capture123',
            'Status' => 'SUCCESS',
            'Date' => '2023-05-06T12:34:56Z',
        ];
    }
}
