<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payment\Event;

class PaymentAssertionSucceeded
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
     *     Token: string
     * }
     */
    public function getRequestBody(): array
    {
        /** @phpstan-ignore-next-line */
        return $this->requestBody;
    }

    /**
     * @return array{
     *     StatusCode: int,
     *     ResponseHeader: array{SpecVersion: string, RequestId: string},
     *     Transaction: array{
     *         Type: string,
     *         Status: string,
     *         Id: string,
     *         Date: string,
     *         Amount: array{Value: int, CurrencyCode: string},
     *         AcquirerName: string,
     *         AcquirerReference: string,
     *         SixTransactionReference: string,
     *         ApprovalCode: string
     *     },
     *     PaymentMeans: array{
     *         Brand: array{PaymentMethod: string, Name: string},
     *         DisplayText: string,
     *         Card: array{
     *             MaskedNumber: string,
     *             ExpYear: string,
     *             ExpMonth: string,
     *             HolderName: string,
     *             CountryCode: string
     *         }
     *     },
     *     Liability: array{
     *         LiabilityShift: bool,
     *         LiabilityEntity: string,
     *         ThreeDs: array{Authenticated: bool, LiabilityShift: bool, Xid: string}
     *     },
     *     Error: null
     * }
     */
    public function getResponseData(): array
    {
        /** @phpstan-ignore-next-line */
        return $this->responseData;
    }
}
