<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract\PaymentPage;

use Symfony\Component\BrowserKit\Response as BrowserResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract\SaferpayApiTestCase;

final class InitializeTest extends SaferpayApiTestCase
{
    public function testFailOnWrongAuthData(): void
    {
        $this->browser->request(
            method: Request::METHOD_POST,
            uri: $this->getUrl(self::INITIALIZATION_ENDPOINT),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode($this->getExamplePayload()),
        );

        /** @var BrowserResponse $response */
        $response = $this->browser->getResponse();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertSaferpayResponse($response, 'PaymentPage/Initialize/fail_on_wrong_auth_data');
    }

    public function testInitializePayment(): void
    {
        $this->browser->request(
            method: Request::METHOD_POST,
            uri: $this->getUrl(self::INITIALIZATION_ENDPOINT),
            server: array_merge([
                'HTTP_AUTHORIZATION' => sprintf('Basic %s', $this->getAuthString()),
            ], self::CONTENT_TYPE_HEADER),
            content: json_encode($this->getExamplePayload()),
        );

        /** @var BrowserResponse $response */
        $response = $this->browser->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSaferpayResponse($response, 'PaymentPage/Initialize/initialize_payment');
    }

    private function getExamplePayload(): array
    {
        return [
            "RequestHeader" => [
                "SpecVersion" => "1.33",
                "CustomerId" => "268229",
                "RequestId" => "3358af17-35c1-4165-a343-c1c86a320f3b",
                "RetryIndicator" => 0
            ],
            "TerminalId" => "17757531",
            "Payment" => [
                "Amount" => [
                    "Value" => "100",
                    "CurrencyCode" => "EUR"
                ],
                "OrderId" => "00000001",
                "Description" => "Description of payment"
            ],
            "ReturnUrl" => [
                "Url" => "https://127.0.0.1:8001/en_US/order/after-pay"
            ]
        ];
    }
}
