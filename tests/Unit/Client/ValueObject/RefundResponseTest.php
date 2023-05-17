<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Client\ValueObject;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\PaymentMeans;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Transaction;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;
use PHPUnit\Framework\TestCase;

final class RefundResponseTest extends TestCase
{
    /** @test */
    public function it_creates_refund_response_vo_from_array(): void
    {
        $response = AssertResponse::fromArray([
            'StatusCode' => 200,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
            ],
            'Transaction' => [
                'Type' => 'REFUND',
                'Status' => 'AUTHORIZED',
                'Id' => '723n4MAjMdhjSAhAKEUdA8jtl9jb',
                'Date' => '2015-01-30T12:45:22.258+01:00',
                'Amount' => [
                    'Value' => '100',
                    'CurrencyCode' => 'CHF',
                ],
                'AcquirerName' => 'Saferpay Test Card',
                'AcquirerReference' => '000000',
                'SixTransactionReference' => '0:0:3:723n4MAjMdhjSAhAKEUdA8jtl9jb',
                'ApprovalCode' => '012345',
                'IssuerReference' => [
                    'TransactionStamp' => '3797496535630697360974',
                ],
            ],
            'PaymentMeans' => [
                'Brand' => [
                    'PaymentMethod' => 'VISA',
                    'Name' => 'VISA Saferpay Test',
                ],
                'DisplayText' => '9123 45xx xxxx 1234',
                'Card' => [
                    'MaskedNumber' => '912345xxxxxx1234',
                    'ExpYear' => 2023,
                    'ExpMonth' => 9,
                    'HolderName' => 'Max Mustermann',
                    'CountryCode' => 'CH',
                ],
            ],
        ]);

        $this->assertResponseHeader($response->getResponseHeader());
        $this->assertTransaction($response->getTransaction());
        $this->assertPaymentMeans($response->getPaymentMeans());
    }

    private function assertResponseHeader(ResponseHeader $responseHeader): void
    {
        $this->assertEquals('1.33', $responseHeader->getSpecVersion());
        $this->assertEquals('b27de121-ffa0-4f1d-b7aa-b48109a88486', $responseHeader->getRequestId());
    }

    private function assertTransaction(Transaction $transaction): void
    {
        $this->assertEquals('REFUND', $transaction->getType());
        $this->assertEquals('AUTHORIZED', $transaction->getStatus());
        $this->assertEquals('723n4MAjMdhjSAhAKEUdA8jtl9jb', $transaction->getId());
        $this->assertEquals('2015-01-30T12:45:22.258+01:00', $transaction->getDate());
        $this->assertEquals('100', $transaction->getAmount()->getValue());
        $this->assertEquals('CHF', $transaction->getAmount()->getCurrencyCode());
        $this->assertEquals('Saferpay Test Card', $transaction->getAcquirerName());
        $this->assertEquals('000000', $transaction->getAcquirerReference());
        $this->assertEquals('0:0:3:723n4MAjMdhjSAhAKEUdA8jtl9jb', $transaction->getSixTransactionReference());
        $this->assertEquals('012345', $transaction->getApprovalCode());
        $this->assertEquals('3797496535630697360974', $transaction->getIssuerReference()->getTransactionStamp());
    }

    private function assertPaymentMeans(PaymentMeans $paymentMeans): void
    {
        $this->assertEquals('VISA', $paymentMeans->getBrand()->getPaymentMethod());
        $this->assertEquals('VISA Saferpay Test', $paymentMeans->getBrand()->getName());
        $this->assertEquals('9123 45xx xxxx 1234', $paymentMeans->getDisplayText());
        $this->assertEquals('912345xxxxxx1234', $paymentMeans->getCard()->getMaskedNumber());
        $this->assertEquals(2023, $paymentMeans->getCard()->getExpirationYear());
        $this->assertEquals(9, $paymentMeans->getCard()->getExpirationMonth());
        $this->assertEquals('Max Mustermann', $paymentMeans->getCard()->getHolderName());
        $this->assertEquals('CH', $paymentMeans->getCard()->getCountryCode());
    }
}
