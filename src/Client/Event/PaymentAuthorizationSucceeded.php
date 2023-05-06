<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\Event;

class PaymentAuthorizationSucceeded
{
    public function __construct(
        private int $paymentId,
        private string $requestUrl,
        private array $requestBody,
        private array $responseData,
    ) {
    }

    public function getPaymentId(): int
    {
        return $this->paymentId;
    }

    public function getRequestUrl(): string
    {
        return $this->requestUrl;
    }

    /**
     * @return array{
     *     TerminalId: string,
     *     Payment: array{
     *         Amount: array{Value: int|null, CurrencyCode: string|null},
     *         OrderId: string|null,
     *         Description: string|null
     *     },
     *     ReturnUrl: array{Url: string|null}
     * }
     */
    public function getRequestBody(): array
    {
        return $this->requestBody;
    }

    /**
     * @return array{
     *     StatusCode: int,
     *     ResponseHeader: array{SpecVersion: string, RequestId: string},
     *     Token: string,
     *     Expiration: string,
     *     RedirectUrl: string
     * }
     */
    public function getResponseData(): array
    {
        return $this->responseData;
    }
}
