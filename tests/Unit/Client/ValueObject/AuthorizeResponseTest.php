<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Client\ValueObject;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;
use PHPUnit\Framework\TestCase;

final class AuthorizeResponseTest extends TestCase
{
    /** @test */
    public function it_creates_authorize_response_vo_from_array(): void
    {
        $response = AuthorizeResponse::fromArray([
            'StatusCode' => 200,
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
            ],
            'Token' => 'sk6jU1jJ7KqO1hgC',
            'Expiration' => '2025-01-30T12:45:22.258+01:00',
            'RedirectUrl' => 'https://www.saferpay.com/api/redirect',
        ]);

        $this->assertResponseHeader($response->getResponseHeader());
        $this->assertEquals('sk6jU1jJ7KqO1hgC', $response->getToken());
        $this->assertEquals('2025-01-30T12:45:22.258+01:00', $response->getExpiration());
        $this->assertEquals('https://www.saferpay.com/api/redirect', $response->getRedirectUrl());
    }

    public function it_creates_authorize_response_vo_with_an_error_from_array(): void
    {
        $response = AuthorizeResponse::fromArray([
            "ResponseHeader" => [
                "SpecVersion" => "1.33",
                "RequestId" => "b27de121-ffa0-4f1d-b7aa-b48109a88486",
            ],
            "Behavior" => "DO_NOT_RETRY",
            "ErrorName" => "VALIDATION_FAILED",
            "ErrorMessage" => "Request validation failed",
            "ErrorDetail" => [
                "TerminalId: The field TerminalId is invalid.",
            ],
        ]);

        $this->assertResponseHeader($response->getResponseHeader());
        $this->assertEquals('DO_NOT_RETRY', $response->getError()->getBehavior());
        $this->assertEquals('VALIDATION_FAILED', $response->getError()->getName());
        $this->assertEquals('Request validation failed', $response->getError()->getMessage());
        $this->assertEquals(['TerminalId: The field TerminalId is invalid.'], $response->getError()->getDetails());
    }

    private function assertResponseHeader(ResponseHeader $responseHeader): void
    {
        $this->assertEquals('1.33', $responseHeader->getSpecVersion());
        $this->assertEquals('b27de121-ffa0-4f1d-b7aa-b48109a88486', $responseHeader->getRequestId());
    }
}
