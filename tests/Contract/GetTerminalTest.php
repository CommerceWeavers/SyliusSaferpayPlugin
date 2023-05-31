<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract;

use Symfony\Component\BrowserKit\Response as BrowserResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetTerminalTest extends SaferpayApiTestCase
{
    public function testGetTerminal(): void
    {
        $this->browser->request(
            method: Request::METHOD_GET,
            uri: $this->getUrl('rest/customers/268229/terminals/17757531'),
            server: array_merge([
                    'HTTP_AUTHORIZATION' => sprintf('Basic %s', $this->getAuthString()),
                    'HTTP_Saferpay-ApiVersion' => '1.33',
                    'HTTP_Saferpay-RequestId' => '5cf3795a-69ff-4bf2-b4b2-b50bf80fcabb',
                ],
                self::CONTENT_TYPE_HEADER,
            ),
        );

        /** @var BrowserResponse $response */
        $response = $this->browser->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSaferpayResponse($response, 'get_terminal');
    }
}
