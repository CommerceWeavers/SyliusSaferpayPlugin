<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract\Transaction;

use Symfony\Component\BrowserKit\Response as BrowserResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract\SaferpayApiTestCase;

final class CaptureTest extends SaferpayApiTestCase
{
    public function testCapturePayment(): void
    {
        $assertData = $this->iFinishedPaymentProcess();

        $this->iCapturePayment($assertData->getTransaction()->getId());

        /** @var BrowserResponse $response */
        $response = $this->browser->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSaferpayResponse($response, 'Transaction/Capture/capture_payment');
    }

    public function testCaptureRefundPayment(): void
    {
        $assertData = $this->iFinishedPaymentProcess();
        $captureData = $this->iCapturePayment($assertData->getTransaction()->getId());
        $refundData = $this->iRefundPayment($captureData->getCaptureId());

        $this->iCapturePayment($refundData->getTransaction()->getId());

        /** @var BrowserResponse $response */
        $response = $this->browser->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSaferpayResponse($response, 'Transaction/Capture/capture_payment');
    }

    public function testFailOnCapturingPaymentTwice(): void
    {
        $assertData = $this->iFinishedPaymentProcess();
        $this->iCapturePayment($assertData->getTransaction()->getId());

        $this->browser->request(
            method: Request::METHOD_POST,
            uri: $this->getUrl(self::CAPTURE_ENDPOINT),
            server: array_merge([
                'HTTP_AUTHORIZATION' => sprintf('Basic %s', $this->getAuthString()),
            ], self::CONTENT_TYPE_HEADER),
            content: json_encode(SaferpayApiTestCase::getCapturePayload($assertData->getTransaction()->getId())),
        );

        /** @var BrowserResponse $response */
        $response = $this->browser->getResponse();

        $this->assertEquals(Response::HTTP_PAYMENT_REQUIRED, $response->getStatusCode());
        $this->assertSaferpayResponse($response, 'Transaction/Capture/fail_on_capturing_payment_twice');
    }
}
