<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract\PaymentPage;

use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\BrowserKit\Response as BrowserResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\Client as PantherClient;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract\SaferpayApiTestCase;

final class AssertTest extends SaferpayApiTestCase
{
    public function testFailOnAssertingNotStartedTransaction(): void
    {
        $authorizeResponse = $this->initializePayment();

        $this->browser->request(
            method: Request::METHOD_POST,
            uri: $this->getUrl(self::ASSERT_ENDPOINT),
            server: array_merge([
                'HTTP_AUTHORIZATION' => sprintf('Basic %s', $this->getAuthString()),
            ], self::CONTENT_TYPE_HEADER),
            content: json_encode($this->getAssertPayload($authorizeResponse->getToken())),
        );

        /** @var BrowserResponse $response */
        $response = $this->browser->getResponse();

        $this->assertEquals(Response::HTTP_PAYMENT_REQUIRED, $response->getStatusCode());
        $this->assertSaferpayResponse($response, 'PaymentPage/Assert/fail_on_asserting_not_started_payment');
    }

    public function testAssertPayment(): void
    {
        $authorizeResponse = $this->initializePayment();

        $panther = self::createPantherClient();
        $panther->get($authorizeResponse->getRedirectUrl());

        $this->iChooseVisaCardAsPaymentMethod($panther);
        $this->iConfirmCardData($panther);
        $this->iChoosePaymentInDollars($panther);
        $this->iProcessWith3dSecureAuthentication($panther);

        $this->browser->request(
            method: Request::METHOD_POST,
            uri: $this->getUrl(self::ASSERT_ENDPOINT),
            server: array_merge([
                'HTTP_AUTHORIZATION' => sprintf('Basic %s', $this->getAuthString()),
            ], self::CONTENT_TYPE_HEADER),
            content: json_encode($this->getAssertPayload($authorizeResponse->getToken())),
        );

        /** @var BrowserResponse $response */
        $response = $this->browser->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSaferpayResponse($response, 'PaymentPage/Assert/assert_payment');
    }

    private function iChooseVisaCardAsPaymentMethod(PantherClient $client): void
    {
        $client->getWebDriver()->findElement(WebDriverBy::className('btn-card-visa'))->click();
    }

    private function iConfirmCardData(PantherClient $client): void
    {
        $client->waitFor('.btn-next');
        $client->getWebDriver()->findElement(WebDriverBy::className('btn-next'))->click();
    }

    private function iChoosePaymentInDollars(PantherClient $client): void
    {
        $client->waitFor('button#chargeInMerchantAmount');
        $client->getWebDriver()->findElement(WebDriverBy::id('chargeInMerchantAmount'))->click();
    }

    private function iProcessWith3dSecureAuthentication(PantherClient $client): void
    {
        $client->waitForVisibility('#SharedThreeDSIFrame');
        $client->switchTo()->frame($client->getWebDriver()->findElement(WebDriverBy::id('SharedThreeDSIFrame')));
        $client->waitFor('input[name="submitButton"]');
        $client->getWebDriver()->findElement(WebDriverBy::name('submitButton'))->click();
        $client->switchTo()->defaultContent();
    }
}
