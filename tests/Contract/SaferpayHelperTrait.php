<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract;

use Ramsey\Uuid\Uuid;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

trait SaferpayHelperTrait
{
    protected HttpBrowser $browser;

    private function initializeBrowser(): void
    {
        $this->browser = new HttpBrowser(HttpClient::create());
    }

    protected function getInitializePayload(): array
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

    protected function getAssertPayload(string $token): array
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
}
