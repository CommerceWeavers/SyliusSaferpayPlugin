<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Client\VO;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse\Liability;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse\PaymentMeans;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse\Transaction;
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

        $this->assertResponseHeader($response->getResponseHeader());
        $this->assertTransaction($response->getTransaction());
        $this->assertPaymentMeans($response->getPaymentMeans());
        $this->assertLiability($response->getLiability());
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
        $this->assertEquals('Saferpay Test Card', $transaction->getAcquirerName());
        $this->assertEquals('000000', $transaction->getAcquirerReference());
        $this->assertEquals('0:0:3:723n4MAjMdhjSAhAKEUdA8jtl9jb', $transaction->getSixTransactionReference());
        $this->assertEquals('012345', $transaction->getApprovalCode());
    }

    private function assertPaymentMeans(PaymentMeans $paymentMeans): void
    {
        $this->assertEquals('VISA', $paymentMeans->getBrand()->getPaymentMethod());
        $this->assertEquals('VISA Saferpay Test', $paymentMeans->getBrand()->getName());
        $this->assertEquals('9123 45xx xxxx 1234', $paymentMeans->getDisplayText());
        $this->assertEquals('912345xxxxxx1234', $paymentMeans->getCard()->getMaskedNumber());
        $this->assertEquals(2015, $paymentMeans->getCard()->getExpirationYear());
        $this->assertEquals(9, $paymentMeans->getCard()->getExpirationMonth());
        $this->assertEquals('Max Mustermann', $paymentMeans->getCard()->getHolderName());
        $this->assertEquals('CH', $paymentMeans->getCard()->getCountryCode());
    }

    private function assertLiability(Liability $liability): void
    {
        $this->assertTrue($liability->getLiabilityShift());
        $this->assertEquals('THREEDS', $liability->getLiableEntity());
        $this->assertTrue($liability->getThreeDs()->getAuthenticated());
        $this->assertTrue($liability->getThreeDs()->getLiabilityShift());
        $this->assertEquals('ARkvCgk5Y1t/BDFFXkUPGX9DUgs=', $liability->getThreeDs()->getXid());
    }
}
