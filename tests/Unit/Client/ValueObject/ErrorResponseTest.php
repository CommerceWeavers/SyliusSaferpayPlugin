<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Client\ValueObject;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\ErrorResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;
use PHPUnit\Framework\TestCase;

final class ErrorResponseTest extends TestCase
{
    /** @test */
    public function it_creates_error_response_vo_for_assert_from_array(): void
    {
        $response = ErrorResponse::forAssert([
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

        $this->assertEquals(402, $response->getStatusCode());
        $this->assertResponseHeader($response->getResponseHeader());
        $this->assertEquals('DO_NOT_RETRY', $response->getBehavior());
        $this->assertEquals('3DS_AUTHENTICATION_FAILED', $response->getName());
        $this->assertEquals('3D-Secure authentication failed', $response->getMessage());
        $this->assertEquals('723n4MAjMdhjSAhAKEUdA8jtl9jb', $response->getTransactionId());
        $this->assertEquals('Card holder information -> Failed', $response->getPayerMessage());
        $this->assertEquals('000000042', $response->getOrderId());
        $this->assertEquals('Assert', $response->getFailedOperation());
    }

    /** @test */
    public function it_creates_error_response_vo_for_capture_from_array(): void
    {
        $response = ErrorResponse::forCapture([
            'StatusCode' => 402,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
            ],
            "Behavior" => "DO_NOT_RETRY",
            "ErrorName" => "TRANSACTION_ALREADY_CAPTURED",
            "ErrorMessage" => "Transaction already captured",
        ]);

        $this->assertEquals(402, $response->getStatusCode());
        $this->assertResponseHeader($response->getResponseHeader());
        $this->assertEquals('DO_NOT_RETRY', $response->getBehavior());
        $this->assertEquals('TRANSACTION_ALREADY_CAPTURED', $response->getName());
        $this->assertEquals('Transaction already captured', $response->getMessage());
        $this->assertEquals('Capture', $response->getFailedOperation());
    }

    /** @test */
    public function it_creates_error_response_vo_for_authorize_from_array(): void
    {
        $response = ErrorResponse::forAuthorize([
            'StatusCode' => 402,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
            ],
            "Behavior" => "DO_NOT_RETRY",
            "ErrorName" => "VALIDATION_FAILED",
            "ErrorMessage" => "Request validation failed",
            "ErrorDetail" => [
                "TerminalId: The field TerminalId is invalid.",
            ],
        ]);

        $this->assertEquals(402, $response->getStatusCode());
        $this->assertResponseHeader($response->getResponseHeader());
        $this->assertEquals('DO_NOT_RETRY', $response->getBehavior());
        $this->assertEquals('VALIDATION_FAILED', $response->getName());
        $this->assertEquals('Request validation failed', $response->getMessage());
        $this->assertEquals(["TerminalId: The field TerminalId is invalid."], $response->getDetail());
        $this->assertEquals('Authorize', $response->getFailedOperation());
    }

    /** @test */
    public function it_creates_error_response_vo_for_refund_from_array(): void
    {
        $response = ErrorResponse::forRefund([
            'StatusCode' => 402,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
            ],
            "Behavior" => "DO_NOT_RETRY",
            "ErrorName" => "TRANSACTION_NOT_FOUND",
            "ErrorMessage" => "Transaction not found",
        ]);

        $this->assertEquals(402, $response->getStatusCode());
        $this->assertResponseHeader($response->getResponseHeader());
        $this->assertEquals('DO_NOT_RETRY', $response->getBehavior());
        $this->assertEquals('TRANSACTION_NOT_FOUND', $response->getName());
        $this->assertEquals('Transaction not found', $response->getMessage());
        $this->assertEquals('Refund', $response->getFailedOperation());
    }

    private function assertResponseHeader(ResponseHeader $responseHeader): void
    {
        $this->assertEquals('1.33', $responseHeader->getSpecVersion());
        $this->assertEquals('b27de121-ffa0-4f1d-b7aa-b48109a88486', $responseHeader->getRequestId());
    }
}
