<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payment\EventDispatcher;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAssertionFailed;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAssertionSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentAuthorizationSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentCaptureSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentRefundFailed;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Event\PaymentRefundSucceeded;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\EventDispatcher\PaymentEventDispatcher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class PaymentEventDispatcherSpec extends ObjectBehavior
{
    function let(MessageBusInterface $eventBus): void
    {
        $this->beConstructedWith($eventBus);
    }

    function it_implements_payment_event_dispatcher_interface(): void
    {
        $this->shouldHaveType(PaymentEventDispatcher::class);
    }

    function it_dispatches_payment_authorization_succeeded_event(
        MessageBusInterface $eventBus,
        PaymentInterface $payment,
    ): void {
        $payload = $this->getExampleAuthorizePayload();
        $response = $this->getExampleAuthorizeResponse();

        $payment->getId()->willReturn(1);

        $eventBus
            ->dispatch(Argument::that(function (PaymentAuthorizationSucceeded $event) use ($payload, $response) {
                return
                    $event->getPaymentId() === 1
                    && $event->getRequestUrl() === 'Payment/v1/PaymentPage/Initialize'
                    && $event->getRequestBody() === $payload
                    && $event->getResponseData() === $response
                ;
            }))
            ->shouldBeCalled()
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->dispatchAuthorizationSucceededEvent(
            $payment,
            'Payment/v1/PaymentPage/Initialize',
            $payload,
            AuthorizeResponse::fromArray($response)
        );
    }

    function it_dispatches_payment_assertion_succeeded_event(
        MessageBusInterface $eventBus,
        PaymentInterface $payment,
    ): void {
        $payload = $this->getExampleAssertPayload();
        $response = $this->getExampleAssertResponse();

        $payment->getId()->willReturn(1);

        $eventBus
            ->dispatch(Argument::that(function (PaymentAssertionSucceeded $event) use ($payload, $response) {
                return
                    $event->getPaymentId() === 1
                    && $event->getRequestUrl() === 'Payment/v1/PaymentPage/Assert'
                    && $event->getRequestBody() === $payload
                    && $event->getResponseData() == $response
                ;
            }))
            ->shouldBeCalled()
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->dispatchAssertionSucceededEvent(
            $payment,
            'Payment/v1/PaymentPage/Assert',
            $payload,
            AssertResponse::fromArray($response)
        );
    }

    function it_dispatches_payment_assertion_failed_event(
        MessageBusInterface $eventBus,
        PaymentInterface $payment,
    ): void {
        $payload = $this->getExampleAssertPayload();
        $response = $this->getExampleAssertErrorResponse();

        $payment->getId()->willReturn(1);

        $eventBus
            ->dispatch(Argument::that(function (PaymentAssertionFailed $event) use ($payload) {
                $response = $event->getResponseData();

                return
                    $event->getPaymentId() === 1
                    && $event->getRequestUrl() === 'Payment/v1/PaymentPage/Assert'
                    && $event->getRequestBody() === $payload
                    && $response['StatusCode'] === 402
                    && $response['Name'] === '3DS_AUTHENTICATION_FAILED'
                    && $response['Message'] === '3D-Secure authentication failed'
                    && $response['Behavior'] === 'DO_NOT_RETRY'
                    && $response['TransactionId'] === 'Q3hd5IbzlnKpvAICv2QdA72QlA1b'
                    && $response['OrderId'] === '000000042'
                    && $response['PayerMessage'] === 'Card holder information -> Failed'
                    && $response['ProcessorName'] === null
                    && $response['ProcessorResult'] === null
                    && $response['ProcessorMessage'] === null
                ;
            }))
            ->shouldBeCalled()
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->dispatchAssertionFailedEvent(
            $payment,
            'Payment/v1/PaymentPage/Assert',
            $payload,
            ErrorResponse::forAssert($response)
        );
    }

    function it_dispatches_payment_capture_succeeded_event(
        MessageBusInterface $eventBus,
        PaymentInterface $payment,
    ): void {
        $payload = $this->getExampleCapturePayload();
        $response = $this->getExampleCaptureResponse();

        $payment->getId()->willReturn(1);

        $eventBus
            ->dispatch(Argument::that(function (PaymentCaptureSucceeded $event) use ($payload, $response) {
                return
                    $event->getPaymentId() === 1
                    && $event->getRequestUrl() === 'Payment/v1/Transaction/Capture'
                    && $event->getRequestBody() === $payload
                    && $event->getResponseData() === $response
                ;
            }))
            ->shouldBeCalled()
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->dispatchCaptureSucceededEvent(
            $payment,
            'Payment/v1/Transaction/Capture',
            $payload,
            CaptureResponse::fromArray($response)
        );
    }

    function it_dispatches_payment_refund_succeeded_event(
        MessageBusInterface $eventBus,
        PaymentInterface $payment,
    ): void {
        $payload = $this->getExampleRefundPayload();
        $response = $this->getExampleRefundResponse();

        $payment->getId()->willReturn(1);

        $eventBus
            ->dispatch(Argument::that(function (PaymentRefundSucceeded $event) use ($payload, $response) {
                return
                    $event->getPaymentId() === 1
                    && $event->getRequestUrl() === 'Payment/v1/Transaction/Refund'
                    && $event->getRequestBody() === $payload
                    && $event->getResponseData() == $response
                ;
            }))
            ->shouldBeCalled()
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->dispatchRefundSucceededEvent(
            $payment,
            'Payment/v1/Transaction/Refund',
            $payload,
            RefundResponse::fromArray($response)
        );
    }

    function it_dispatches_payment_refund_failed_event(
        MessageBusInterface $eventBus,
        PaymentInterface $payment,
    ): void {
        $payload = $this->getExampleRefundPayload();
        $response = $this->getExampleRefundErrorResponse();

        $payment->getId()->willReturn(1);

        $eventBus
            ->dispatch(Argument::that(function (PaymentRefundFailed $event) use ($payload) {
                $response = $event->getResponseData();

                return
                    $event->getPaymentId() === 1
                    && $event->getRequestUrl() === 'Payment/v1/Transaction/Refund'
                    && $event->getRequestBody() === $payload
                    && $response['StatusCode'] === 402
                    && $response['Error']['Name'] === 'TRANSACTION_NOT_FOUND'
                    && $response['Error']['Message'] === 'Transaction not found'
                    && $response['Error']['Behavior'] === 'DO_NOT_RETRY'
                    && $response['Error']['TransactionId'] === null
                    && $response['Error']['OrderId'] === null
                    && $response['Error']['PayerMessage'] === null
                    && $response['Error']['ProcessorName'] === null
                    && $response['Error']['ProcessorResult'] === null
                    && $response['Error']['ProcessorMessage'] === null
                ;
            }))
            ->shouldBeCalled()
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->dispatchRefundFailedEvent(
            $payment,
            'Payment/v1/Transaction/Refund',
            $payload,
            RefundResponse::fromArray($response)
        );
    }

    private function getExampleAuthorizePayload(): array
    {
        return [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'TerminalId' => 'TERMINAL-ID',
            'Payment' => [
                'Amount' => [
                    'Value' => 10000,
                    'CurrencyCode' => 'CHF',
                ],
                'OrderId' => '000000001',
                'Description' => 'Payment for order 000000001',
            ],
            'ReturnUrl' => [
                'Url' => 'https://example.com/after',
            ],
        ];
    }

    private function getExampleAuthorizeResponse(): array
    {
        return [
            'StatusCode' => 200,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'abc123',
            ],
            'Token' => '234uhfh78234hlasdfh8234e1234',
            'Expiration' => '2015-01-30T12:45:22.258+01:00',
            'RedirectUrl' => 'https://www.saferpay.com/vt2/api/...',
            'ErrorName' => null,
            'ErrorMessage' => null,
        ];
    }

    public function getExampleAssertPayload(): array
    {
        return [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Token' => 'TOKEN',
        ];
    }

    private function getExampleAssertResponse(): array
    {
        return [
            'StatusCode' => 200,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'abc123',
            ],
            'Transaction' => [
                'Type' => 'PAYMENT',
                'Status' => 'AUTHORIZED',
                'Id' => '723n4MAjMdhjSAhAKEUdA8jtl9jb',
                'Date' => '2015-01-30T12:45:22.258+01:00',
                'Amount' => [
                    'Value' => '100',
                    'CurrencyCode' => 'CHF',
                ],
                'SixTransactionReference' => '0:0:3:723n4MAjMdhjSAhAKEUdA8jtl9jb',
                'OrderId' => '000000001',
                'CaptureId' => null,
                'AcquirerName' => 'Saferpay Test Card',
                'AcquirerReference' => '000000',
                'ApprovalCode' => '012345',
                'IssuerReference' => null,
            ],
            'PaymentMeans' => [
                'Brand' => [
                    'PaymentMethod' => 'VISA',
                    'Name' => 'VISA Saferpay Test',
                ],
                'DisplayText' => '9123 45xx xxxx 1234',
                'Card' => [
                    'MaskedNumber' => '912345xxxxxx1234',
                    'ExpYear' => 2015,
                    'ExpMonth' => 9,
                    'HolderName' => 'Max Mustermann',
                    'CountryCode' => 'CH',
                ],
                'BankAccount' => null,
                'PayPal' => null,
            ],
            'Payer' => null,
            'Liability' => [
                'LiabilityShift' => true,
                'LiableEntity' => 'THREEDS',
                'ThreeDs' => [
                    'Authenticated' => true,
                    'LiabilityShift' => true,
                    'Xid' => 'ARkvCgk5Y1t/BDFFXkUPGX9DUgs=',
                ],
                'InPsd2Scope' => null,
            ],
            'Error' => null,
        ];
    }

    private function getExampleAssertErrorResponse(): array
    {
        return [
            'StatusCode' => 402,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'abc123',
            ],
            'Behavior' => 'DO_NOT_RETRY',
            'ErrorName' => '3DS_AUTHENTICATION_FAILED',
            'ErrorMessage' => '3D-Secure authentication failed',
            'TransactionId' => 'Q3hd5IbzlnKpvAICv2QdA72QlA1b',
            'PayerMessage' => 'Card holder information -> Failed',
            'OrderId' => '000000042',
        ];
    }

    private function getExampleCapturePayload(): array
    {
        return [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'TransactionReference' => [
                'TransactionId' => '123456789',
            ],
        ];
    }

    private function getExampleCaptureResponse(): array
    {
        return [
            'StatusCode' => 200,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'abc123',
            ],
            'CaptureId' => '723n4MAjMdhjSAhAKEUdA8jtl9jb',
            'Status' => 'CAPTURED',
            'Date' => '2015-01-30T12:45:22.258+01:00',
            'Error' => null,
        ];
    }

    private function getExampleRefundPayload(): array
    {
        return [
            'RequestHeader' => [
                'SpecVersion' => '1.33',
                'CustomerId' => 'CUSTOMER-ID',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
                'RetryIndicator' => 0,
            ],
            'Refund' => [
                'Amount' => [
                    'Value' => 10000,
                    'CurrencyCode' => 'CHF',
                ],
            ],
            'CaptureReference' => [
                'CaptureId' => '0d7OYrAInYCWSASdzSh3bbr4jrSb_c',
            ],
        ];
    }

    private function getExampleRefundResponse(): array
    {
        return [
            'StatusCode' => 200,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => '1f97328d-651f-44be-94d6-fbb0a4d2f117',
            ],
            'Transaction' => [
                'Type' => 'REFUND',
                'Status' => 'AUTHORIZED',
                'Id' => 'Q7Wf4lb07WtbtAEd6j30bx4UhdvA',
                'Date' => '2023-04-26T10:41:53.792+02:00',
                'Amount' => [
                    'Value' => '10000',
                    'CurrencyCode' => 'CHF',
                ],
                'SixTransactionReference' => '0:0:3:Q7Wf4lb07WtbtAEd6j30bx4UhdvA',
                'CaptureId' => null,
                'OrderId' => null,
                'AcquirerName' => 'VISA Saferpay Test',
                'AcquirerReference' => '50953026375',
                'ApprovalCode' => '283702',
                'IssuerReference' => [
                    'TransactionStamp' => '3797496535630697360974',
                ],
            ],
            'PaymentMeans' => [
                'Brand' => [
                    'PaymentMethod' => 'VISA',
                    'Name' => 'VISA Saferpay Test',
                ],
                'DisplayText' => 'xxxx xxxx xxxx 0007',
                'Card' => [
                    'MaskedNumber' => 'xxxxxxxxxxxx0007',
                    'ExpYear' => 2023,
                    'ExpMonth' => 4,
                    'HolderName' => 'Yamada Taro',
                    'CountryCode' => 'JP',
                ],
                'BankAccount' => null,
                'PayPal' => null,
            ],
            'Error' => null,
        ];
    }

    private function getExampleRefundErrorResponse(): array
    {
        return [
            'StatusCode' => 402,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'abc123',
            ],
            'Behavior' => 'DO_NOT_RETRY',
            'ErrorName' => 'TRANSACTION_NOT_FOUND',
            'ErrorMessage' => 'Transaction not found',
        ];
    }
}
