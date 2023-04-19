<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Client\VO;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;
use PHPUnit\Framework\TestCase;

final class CaptureResponseTest extends TestCase
{
    /** @test */
    public function it_creates_capture_response_vo_from_array(): void
    {
        $response = CaptureResponse::fromArray([
            'ResponseHeader' => [
                'SpecVersion' => '1.33',
                'RequestId' => 'b27de121-ffa0-4f1d-b7aa-b48109a88486',
            ],
            'CaptureId' => '723n4MAjMdhjSAhAKEUdA8jtl9jb',
            'Status' => 'CAPTURED',
            'Date' => '2015-01-30T12:45:22.258+01:00',
        ]);

        $this->assertResponseHeader($response->getResponseHeader());
        $this->assertEquals('723n4MAjMdhjSAhAKEUdA8jtl9jb', $response->getCaptureId());
        $this->assertEquals('CAPTURED', $response->getStatus());
        $this->assertEquals('2015-01-30T12:45:22.258+01:00', $response->getDate());
    }

    private function assertResponseHeader(ResponseHeader $responseHeader): void
    {
        $this->assertEquals('1.33', $responseHeader->getSpecVersion());
        $this->assertEquals('b27de121-ffa0-4f1d-b7aa-b48109a88486', $responseHeader->getRequestId());
    }
}
