<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract;

use ApiTestCase\JsonApiTestCase;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCaseTrait;

abstract class SaferpayApiTestCase extends JsonApiTestCase
{
    use PantherTestCaseTrait;
    use SaferpayHelperTrait;

    protected const INITIALIZATION_ENDPOINT = '/Payment/v1/PaymentPage/Initialize';

    protected const ASSERT_ENDPOINT = '/Payment/v1/PaymentPage/Assert';

    protected const CONTENT_TYPE_HEADER = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->expectedResponsesPath = __DIR__ . '/.responses';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeBrowser();
    }

    protected function assertSaferpayResponse(Response $response, string $filename): void
    {
        parent::assertResponseContent($this->prettifyJson($response->getContent()), $filename, 'json');
    }

    public function initializePayment(): AuthorizeResponse
    {
        $this->browser->request(
            method: Request::METHOD_POST,
            uri: $this->getUrl(self::INITIALIZATION_ENDPOINT),
            server: array_merge(
                ['HTTP_AUTHORIZATION' => sprintf('Basic %s', $this->getAuthString())],
                self::CONTENT_TYPE_HEADER,
            ),
            content: json_encode($this->getInitializePayload()),
        );

        $response = json_decode($this->browser->getResponse()->getContent(), true);
        $response['StatusCode'] = 200;

        return AuthorizeResponse::fromArray($response);
    }


    protected function getAuthString(): string
    {
        $user = getenv('SAFERPAY_TEST_API_USER');
        $password = getenv('SAFERPAY_TEST_API_PASSWORD');

        return base64_encode(sprintf('%s:%s', $user, $password));
    }

    protected function getSaferpayBaseUrl(): string
    {
        return getenv('SAFERPAY_TEST_API_URL');
    }

    protected function getUrl(string $endpoint): string
    {
        $baseUrl = getenv('SAFERPAY_TEST_API_URL');

        return sprintf('%s%s', $baseUrl, $endpoint);
    }

    protected function get($id): ?object
    {
        if (property_exists(static::class, 'container')) {
            return self::$kernel->getContainer()->get($id);
        }

        return parent::get($id);
    }
}
