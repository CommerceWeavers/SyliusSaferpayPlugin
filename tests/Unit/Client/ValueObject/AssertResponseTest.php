<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Client\ValueObject;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Liability;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\PaymentMeans;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Transaction;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;
use PHPUnit\Framework\TestCase;

final class AssertResponseTest extends TestCase
{
    /** @test */
    public function it_creates_assert_response_vo_from_array(): void
    {
        $response = AssertResponse::fromArray([
            'StatusCode' => 200,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
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
                'OrderId' => '000000001',
                'AcquirerName' => 'Saferpay Test Card',
                'AcquirerReference' => '000000',
                'SixTransactionReference' => '0:0:3:723n4MAjMdhjSAhAKEUdA8jtl9jb',
                'ApprovalCode' => '012345',
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
            ],
            'Liability' => [
                'LiabilityShift' => true,
                'LiableEntity' => 'THREEDS',
                'ThreeDs' => [
                    'Authenticated' => true,
                    'LiabilityShift' => true,
                    'Xid' => 'ARkvCgk5Y1t/BDFFXkUPGX9DUgs=',
                ],
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseHeader($response->getResponseHeader());

        $transaction = $response->getTransaction();
        $this->assertTransaction($response->getTransaction());
        $this->assertEquals('Saferpay Test Card', $transaction->getAcquirerName());
        $this->assertEquals('000000', $transaction->getAcquirerReference());

        $paymentMeans = $response->getPaymentMeans();
        $this->assertEquals('VISA', $paymentMeans->getBrand()->getPaymentMethod());
        $this->assertEquals('VISA Saferpay Test', $paymentMeans->getBrand()->getName());
        $this->assertEquals('9123 45xx xxxx 1234', $paymentMeans->getDisplayText());
        $this->assertEquals('912345xxxxxx1234', $paymentMeans->getCard()->getMaskedNumber());
        $this->assertEquals(2015, $paymentMeans->getCard()->getExpirationYear());
        $this->assertEquals(9, $paymentMeans->getCard()->getExpirationMonth());
        $this->assertEquals('Max Mustermann', $paymentMeans->getCard()->getHolderName());
        $this->assertEquals('CH', $paymentMeans->getCard()->getCountryCode());
        $this->assertNull($paymentMeans->getPayPal());

        $liability = $response->getLiability();
        $this->assertTrue($liability->getLiabilityShift());
        $this->assertEquals('THREEDS', $liability->getLiableEntity());
        $this->assertTrue($liability->getThreeDs()->getAuthenticated());
        $this->assertTrue($liability->getThreeDs()->getLiabilityShift());
        $this->assertEquals('ARkvCgk5Y1t/BDFFXkUPGX9DUgs=', $liability->getThreeDs()->getXid());
    }

    /** @test */
    public function it_creates_assert_paypal_response_vo_from_array(): void
    {
        $response = AssertResponse::fromArray([
            'StatusCode' => 200,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
            ],
            'Transaction' => [
                'Type' => 'PAYMENT',
                'Status' => 'AUTHORIZED',
                'Id' => '723n4MAjMdhjSAhAKEUdA8jtl9jb',
                'Date' => '2015-01-30T12:45:22.258+01:00',
                'Amount' => [
                    'Value' => '100',
                    'CurrencyCode' => 'CHF'
                ],
                'OrderId' => '000000001',
                'AcquirerName' => 'PayPal Saferpay Test',
                'SixTransactionReference' => '0:0:3:723n4MAjMdhjSAhAKEUdA8jtl9jb',
                'ApprovalCode' => '012345',
            ],
            'PaymentMeans' => [
                'Brand' => [
                    'PaymentMethod' => 'PAYPAL',
                    'Name' => 'PayPal'
                ],
                'DisplayText' => 'PayPal',
                'PayPal' => [
                    'PayerId' => '59e803d5-a697-420b-a9cf-cd73d85bcff5',
                    'SellerProtectionStatus' => 'ELIGIBLE',
                    'Email' => 'paypal@email.com'
                ]
            ],
            'Payer' => [
                'IpAddress' => '1.1.1.1',
                'IpLocation' => 'PL'
            ],
            'Liability' => [
                'LiabilityShift' => false,
                'LiableEntity' => 'MERCHANT',
                'InPsd2Scope' => 'UNKNOWN'
            ]
        ]);

        $this->assertResponseHeader($response->getResponseHeader());

        $transaction = $response->getTransaction();
        $this->assertTransaction($response->getTransaction());
        $this->assertEquals('PayPal Saferpay Test', $transaction->getAcquirerName());
        $this->assertNull($transaction->getAcquirerReference());

        $paymentMeans = $response->getPaymentMeans();
        $this->assertEquals('PAYPAL', $paymentMeans->getBrand()->getPaymentMethod());
        $this->assertEquals('PayPal', $paymentMeans->getBrand()->getName());
        $this->assertEquals('PayPal', $paymentMeans->getDisplayText());
        $this->assertNull($paymentMeans->getCard());
        $this->assertEquals('59e803d5-a697-420b-a9cf-cd73d85bcff5', $paymentMeans->getPayPal()->getPayerId());
        $this->assertEquals('ELIGIBLE', $paymentMeans->getPayPal()->getSellerProtectionStatus());
        $this->assertEquals('paypal@email.com', $paymentMeans->getPayPal()->getEmail());

        $liability = $response->getLiability();
        $this->assertFalse($liability->getLiabilityShift());
        $this->assertEquals('MERCHANT', $liability->getLiableEntity());
        $this->assertEquals('UNKNOWN', $liability->getInPsd2Scope());
    }

    /** @test */
    public function it_creates_assert_response_vo_with_an_error_from_array(): void
    {
        $response = AssertResponse::fromArray([
            'StatusCode' => 402,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
            ],
            'Behavior' => 'DO_NOT_RETRY',
            'ErrorName' => '3DS_AUTHENTICATION_FAILED',
            'ErrorMessage' => '3D-Secure authentication failed',
            'TransactionId' => '723n4MAjMdhjSAhAKEUdA8jtl9jb',
            'PayerMessage' => 'Card holder information -> Failed',
            'OrderId' => '000000042',
        ]);

        $this->assertResponseHeader($response->getResponseHeader());
        $this->assertEquals(402, $response->getStatusCode());
        $this->assertEquals('DO_NOT_RETRY', $response->getError()->getBehavior());
        $this->assertEquals('3DS_AUTHENTICATION_FAILED', $response->getError()->getName());
        $this->assertEquals('3D-Secure authentication failed', $response->getError()->getMessage());
        $this->assertEquals('723n4MAjMdhjSAhAKEUdA8jtl9jb', $response->getError()->getTransactionId());
        $this->assertEquals('Card holder information -> Failed', $response->getError()->getPayerMessage());
        $this->assertEquals('000000042', $response->getError()->getOrderId());
    }

    private function assertResponseHeader(ResponseHeader $responseHeader): void
    {
        $this->assertEquals('1.33', $responseHeader->getSpecVersion());
        $this->assertEquals('b27de121-ffa0-4f1d-b7aa-b48109a88486', $responseHeader->getRequestId());
    }

    private function assertTransaction(Transaction $transaction): void
    {
        $this->assertEquals('PAYMENT', $transaction->getType());
        $this->assertEquals('AUTHORIZED', $transaction->getStatus());
        $this->assertEquals('723n4MAjMdhjSAhAKEUdA8jtl9jb', $transaction->getId());
        $this->assertEquals('2015-01-30T12:45:22.258+01:00', $transaction->getDate());
        $this->assertEquals('100', $transaction->getAmount()->getValue());
        $this->assertEquals('CHF', $transaction->getAmount()->getCurrencyCode());
        $this->assertEquals('0:0:3:723n4MAjMdhjSAhAKEUdA8jtl9jb', $transaction->getSixTransactionReference());
        $this->assertEquals('012345', $transaction->getApprovalCode());
    }
}
