<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract\PaymentPage;

use Symfony\Component\BrowserKit\Response as BrowserResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract\SaferpayApiTestCase;

final class AssertTest extends SaferpayApiTestCase
{
    public function testAssertPayment(): void
    {
        $initializeData = $this->iInitializePayment();

        $this->iOpen($initializeData->getRedirectUrl());
        $this->iConfirmCardData();
        $this->iProcessSuccessfully3dSecureAuthentication();

        $this->iAssertPayment($initializeData->getToken());

        /** @var BrowserResponse $response */
        $response = $this->browser->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSaferpayResponse($response, 'PaymentPage/Assert/assert_payment');
    }

    public function testFailOnAssertingNotStartedTransaction(): void
    {
        $authorizeResponse = $this->iInitializePayment();

        $this->browser->request(
            method: Request::METHOD_POST,
            uri: $this->getUrl(self::ASSERT_ENDPOINT),
            server: array_merge([
                'HTTP_AUTHORIZATION' => sprintf('Basic %s', $this->getAuthString()),
            ], self::CONTENT_TYPE_HEADER),
            content: json_encode(SaferpayApiTestCase::getAssertPayload($authorizeResponse->getToken())),
        );

        /** @var BrowserResponse $response */
        $response = $this->browser->getResponse();

        $this->assertEquals(Response::HTTP_PAYMENT_REQUIRED, $response->getStatusCode());
        $this->assertSaferpayResponse($response, 'PaymentPage/Assert/fail_on_asserting_not_started_payment');
    }
}
