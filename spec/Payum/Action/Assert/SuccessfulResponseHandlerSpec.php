<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Assert;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\PaymentMeans;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Transaction;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;

class SuccessfulResponseHandlerSpec extends ObjectBehavior
{
    function it_handles_the_successful_payment(
        PaymentInterface $payment,
        AssertResponse $response,
        Transaction $transaction,
    ): void {
        $payment->getDetails()->willReturn([
            'some_key' => 'some_value',
        ]);
        $payment
            ->setDetails([
                'some_key' => 'some_value',
                'transaction_id' => 'some_transaction_id',
                'status' => 'some_status',
                'payment_means' => [
                    'Brand' => [
                        'Name' => 'VISA',
                        'PaymentMethod' => 'VISA',
                    ],
                    'DisplayText' => 'VISA XXXX-XXXX-XXXX-1111',
                    'Card' => [
                        'MaskedNumber' => 'XXXX-XXXX-XXXX-1111',
                        'ExpYear' => 2025,
                        'ExpMonth' => 12,
                        'HolderName' => 'John Doe',
                        'CountryCode' => 'CH',
                    ],
                    'BankAccount' => null,
                    'PayPal' => null,
                ],
            ])
            ->shouldBeCalled()
        ;

        $response->getTransaction()->willReturn($transaction);
        $response->getPaymentMeans()->willReturn(PaymentMeans::fromArray([
            'Brand' => [
                'PaymentMethod' => 'VISA',
                'Name' => 'VISA',
            ],
            'DisplayText' => 'VISA XXXX-XXXX-XXXX-1111',
            'Card' => [
                'MaskedNumber' => 'XXXX-XXXX-XXXX-1111',
                'ExpYear' => 2025,
                'ExpMonth' => 12,
                'HolderName' => 'John Doe',
                'CountryCode' => 'CH',
            ],
        ]));

        $transaction->getStatus()->willReturn('some_status');
        $transaction->getId()->willReturn('some_transaction_id');

        $this->handle($payment, $response);
    }
}
