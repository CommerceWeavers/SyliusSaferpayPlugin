<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract\Transaction;

use Symfony\Component\BrowserKit\Response as BrowserResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract\SaferpayApiTestCase;

final class RefundTest extends SaferpayApiTestCase
{
    public function testRefundPayment(): void
    {
        $assertData = $this->iFinishedPaymentProcess();
        $captureData = $this->iCapturePayment($assertData->getTransaction()->getId());

        $this->iRefundPayment($captureData->getCaptureId());

        /** @var BrowserResponse $response */
        $response = $this->browser->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSaferpayResponse($response, 'Transaction/Refund/refund_payment');
    }
}
