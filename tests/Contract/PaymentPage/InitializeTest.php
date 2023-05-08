<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract\PaymentPage;

use Symfony\Component\BrowserKit\Response as BrowserResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract\SaferpayApiTestCase;

final class InitializeTest extends SaferpayApiTestCase
{

    public function testInitializePayment(): void
    {
        $this->browser->request(
            method: Request::METHOD_POST,
            uri: $this->getUrl(self::INITIALIZATION_ENDPOINT),
            server: array_merge([
                'HTTP_AUTHORIZATION' => sprintf('Basic %s', $this->getAuthString()),
            ], self::CONTENT_TYPE_HEADER),
            content: json_encode(SaferpayApiTestCase::getInitializePayload()),
        );

        /** @var BrowserResponse $response */
        $response = $this->browser->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSaferpayResponse($response, 'PaymentPage/Initialize/initialize_payment');
    }

    public function testFailOnWrongAuthData(): void
    {
        $this->browser->request(
            method: Request::METHOD_POST,
            uri: $this->getUrl(self::INITIALIZATION_ENDPOINT),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode(SaferpayApiTestCase::getInitializePayload()),
        );

        /** @var BrowserResponse $response */
        $response = $this->browser->getResponse();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertSaferpayResponse($response, 'PaymentPage/Initialize/fail_on_wrong_auth_data');
    }
}
