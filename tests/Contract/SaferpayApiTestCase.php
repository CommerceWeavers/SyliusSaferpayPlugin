<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract;

use ApiTestCase\JsonApiTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Panther\PantherTestCaseTrait;

abstract class SaferpayApiTestCase extends JsonApiTestCase
{
    use PantherTestCaseTrait;
    use SaferpayHelperTrait;

    protected const ASSERT_ENDPOINT = '/Payment/v1/PaymentPage/Assert';

    protected const CAPTURE_ENDPOINT = '/Payment/v1/Transaction/Capture';

    protected const INITIALIZATION_ENDPOINT = '/Payment/v1/PaymentPage/Initialize';

    protected const REFUND_ENDPOINT = '/Payment/v1/Transaction/Refund';

    protected const CONTENT_TYPE_HEADER = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];

    protected HttpBrowser $browser;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->expectedResponsesPath = __DIR__ . '/.responses';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->browser = new HttpBrowser(HttpClient::create());
        self::createPantherClient();
    }

    protected function assertSaferpayResponse(Response $response, string $filename): void
    {
        parent::assertResponseContent($this->prettifyJson($response->getContent()), $filename, 'json');
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

    protected static function getAssertPayload(string $token): array
    {
        return [
            "RequestHeader" => [
                "SpecVersion" => "1.33",
                "CustomerId" => "268229",
                "RequestId" => Uuid::uuid4(),
                "RetryIndicator" => 0
            ],
            "Token" => $token,
        ];
    }

    protected static function getInitializePayload(): array
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
                    "Value" => "1000",
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

    protected static function getCapturePayload(string $transactionId): array
    {
        return [
            "RequestHeader" => [
                "SpecVersion" => "1.33",
                "CustomerId" => "268229",
                "RequestId" => "3358af17-35c1-4165-a343-c1c86a320f3b",
                "RetryIndicator" => 0
            ],
            "TransactionReference" => [
                "TransactionId" => $transactionId,
            ],
        ];
    }

    protected static function getRefundPayload(string $captureId): array
    {
        return [
            "RequestHeader" => [
                "SpecVersion" => "1.33",
                "CustomerId" => "268229",
                "RequestId" => "3358af17-35c1-4165-a343-c1c86a320f3b",
                "RetryIndicator" => 0
            ],
            "Refund" => [
                "Amount" => [
                    "Value" => "1000",
                    "CurrencyCode" => "EUR"
                ],
            ],
            "CaptureReference" => [
                "CaptureId" => $captureId,
            ]
        ];
    }
}
