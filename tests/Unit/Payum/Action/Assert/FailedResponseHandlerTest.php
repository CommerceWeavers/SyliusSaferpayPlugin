<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\Action\Assert;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Assert\FailedResponseHandler;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\ErrorName;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\PaymentInterface;

final class FailedResponseHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_handles_the_cancelled_payment(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $response = $this->prophesize(ErrorResponse::class);

        $payment->getDetails()->willReturn([
            'some_key' => 'some_value',
        ]);
        $payment
            ->setDetails([
                'some_key' => 'some_value',
                'transaction_id' => 'some_transaction_id',
                'status' => StatusAction::STATUS_CANCELLED,
            ])
            ->shouldBeCalled()
        ;

        $response->getTransactionId()->willReturn('some_transaction_id');
        $response->getName()->willReturn(ErrorName::TRANSACTION_ABORTED);

        (new FailedResponseHandler())->handle($payment->reveal(), $response->reveal());
    }

    public function test_it_handles_the_failed_payment(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $response = $this->prophesize(ErrorResponse::class);

        $payment->getDetails()->willReturn([
            'some_key' => 'some_value',
        ]);
        $payment
            ->setDetails([
                'some_key' => 'some_value',
                'transaction_id' => 'some_transaction_id',
                'status' => StatusAction::STATUS_FAILED,
            ])
            ->shouldBeCalled()
        ;

        $response->getTransactionId()->willReturn('some_transaction_id');
        $response->getName()->willReturn(ErrorName::TRANSACTION_DECLINED);

        (new FailedResponseHandler())->handle($payment->reveal(), $response->reveal());
    }
}
