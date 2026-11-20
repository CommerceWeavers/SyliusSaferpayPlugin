<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AuthorizeResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\RefundResponse;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\Client as PantherClient;

/**
 * @property ?PantherClient $pantherClient
 * @property HttpBrowser $browser
 */
trait SaferpayHelperTrait
{
    public function iInitializePayment(): AuthorizeResponse
    {
        $this->browser->request(
            method: Request::METHOD_POST,
            uri: $this->getUrl(self::INITIALIZATION_ENDPOINT),
            server: array_merge(
                ['HTTP_AUTHORIZATION' => sprintf('Basic %s', $this->getAuthString())],
                self::CONTENT_TYPE_HEADER,
            ),
            content: json_encode(self::getInitializePayload()),
        );

        $response = json_decode($this->browser->getResponse()->getContent(), true);
        $response['StatusCode'] = 200;

        return AuthorizeResponse::fromArray($response);
    }

    public function iAssertPayment(string $token): AssertResponse
    {
        $this->browser->request(
            method: Request::METHOD_POST,
            uri: $this->getUrl(self::ASSERT_ENDPOINT),
            server: array_merge([
                'HTTP_AUTHORIZATION' => sprintf('Basic %s', $this->getAuthString()),
            ], self::CONTENT_TYPE_HEADER),
            content: json_encode(SaferpayApiTestCase::getAssertPayload($token)),
        );

        $response = json_decode($this->browser->getResponse()->getContent(), true);
        $response['StatusCode'] = 200;

        return AssertResponse::fromArray($response);
    }

    public function iCapturePayment(string $transactionId): CaptureResponse
    {
        $this->browser->request(
            method: Request::METHOD_POST,
            uri: $this->getUrl(self::CAPTURE_ENDPOINT),
            server: array_merge([
                'HTTP_AUTHORIZATION' => sprintf('Basic %s', $this->getAuthString()),
            ], self::CONTENT_TYPE_HEADER),
            content: json_encode(SaferpayApiTestCase::getCapturePayload($transactionId)),
        );

        $response = json_decode($this->browser->getResponse()->getContent(), true);
        $response['StatusCode'] = 200;

        return CaptureResponse::fromArray($response);
    }

    public function iRefundPayment(string $captureId): RefundResponse
    {
        $this->browser->request(
            method: Request::METHOD_POST,
            uri: $this->getUrl(self::REFUND_ENDPOINT),
            server: array_merge([
                'HTTP_AUTHORIZATION' => sprintf('Basic %s', $this->getAuthString()),
            ], self::CONTENT_TYPE_HEADER),
            content: json_encode(SaferpayApiTestCase::getRefundPayload($captureId)),
        );

        $response = json_decode($this->browser->getResponse()->getContent(), true);
        $response['StatusCode'] = 200;

        return RefundResponse::fromArray($response);
    }

    protected function iOpen(string $url): void
    {
        self::$pantherClient->get($url);
    }

    protected function iConfirmCardData(): void
    {
        self::$pantherClient->waitFor('#btn-card-pay');
        self::$pantherClient->getWebDriver()->findElement(WebDriverBy::id('btn-card-pay'))->click();
    }

    protected function iProcessSuccessfully3dSecureAuthentication(): void
    {
        self::$pantherClient->waitForVisibility('#SharedThreeDSIFrame');
        self::$pantherClient->switchTo()->frame(self::$pantherClient->getWebDriver()->findElement(WebDriverBy::id('SharedThreeDSIFrame')));
        self::$pantherClient->waitFor('input[name="submitButton"]');
        self::$pantherClient->getWebDriver()->findElement(WebDriverBy::name('submitButton'))->click();
        self::$pantherClient->switchTo()->defaultContent();

        // the payment is only finished once Saferpay leaves the 3-D Secure page; asserting earlier returns no transaction
        self::$pantherClient->getWebDriver()->wait(30)->until(
            static fn ($driver): bool => !str_contains((string) $driver->getCurrentURL(), 'SharedThreeDS'),
        );
    }

    protected function iFinishedPaymentProcess(): AssertResponse
    {
        $initializeData = $this->iInitializePayment();

        $this->iOpen($initializeData->getRedirectUrl());
        $this->iConfirmCardData();
        $this->iProcessSuccessfully3dSecureAuthentication();

        return $this->iAssertPayment($initializeData->getToken());
    }
}
