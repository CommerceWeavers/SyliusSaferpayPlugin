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
    protected function iOpen(string $url): void
    {
        self::$pantherClient->get($url);
    }

    protected function iChooseVisaCardAsPaymentMethod(): void
    {
        self::$pantherClient->getWebDriver()->findElement(WebDriverBy::className('btn-card-visa'))->click();
    }

    protected function iConfirmCardData(): void
    {
        self::$pantherClient->waitFor('.btn-next');
        self::$pantherClient->getWebDriver()->findElement(WebDriverBy::className('btn-next'))->click();
    }

    protected function iChoosePaymentInDollars(): void
    {
        self::$pantherClient->waitFor('button#chargeInMerchantAmount');
        self::$pantherClient->getWebDriver()->findElement(WebDriverBy::id('chargeInMerchantAmount'))->click();
    }

    protected function iProcessSuccessfully3dSecureAuthentication(): void
    {
        self::$pantherClient->waitForVisibility('#SharedThreeDSIFrame');
        self::$pantherClient->switchTo()->frame(self::$pantherClient->getWebDriver()->findElement(WebDriverBy::id('SharedThreeDSIFrame')));
        self::$pantherClient->waitFor('input[name="submitButton"]');
        self::$pantherClient->getWebDriver()->findElement(WebDriverBy::name('submitButton'))->click();
        self::$pantherClient->switchTo()->defaultContent();
    }

    protected function iFinishedPaymentProcess(): AssertResponse
    {
        $initializeData = $this->iInitializePayment();

        $this->iOpen($initializeData->getRedirectUrl());
        $this->iChooseVisaCardAsPaymentMethod();
        $this->iConfirmCardData();
        $this->iChoosePaymentInDollars();
        $this->iProcessSuccessfully3dSecureAuthentication();

        return $this->iAssertPayment($initializeData->getToken());
    }

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
}
