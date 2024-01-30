<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Assert;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\ErrorName;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\StatusAction;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;

final class FailedResponseHandlerSpec extends ObjectBehavior
{
    function it_handles_the_cancelled_payment(
        PaymentInterface $payment,
        ErrorResponse $response,
    ): void {
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

        $this->handle($payment, $response);
    }

    function it_handles_the_failed_payment(
        PaymentInterface $payment,
        ErrorResponse $response,
    ): void {
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

        $this->handle($payment, $response);
    }
}
