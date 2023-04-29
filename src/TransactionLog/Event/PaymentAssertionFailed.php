<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Event;

final class PaymentAssertionFailed
{
    public function __construct(
        private string $requestUrl,
        private array $requestBody,
        private array $responseData,
    ) {
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
        return $this->requestBody;
    }

    /**
     * @return array{
     *     StatusCode: int,
     *     ResponseHeader: array{SpecVersion: string, RequestId: string},
     *     Transaction: null,
     *     PaymentMeans: null,
     *     Liability: null,
     *     Error: array{
     *         Name: string,
     *         Message: string,
     *         Behavior: string,
     *         TransactionId: string,
     *         OrderId: string,
     *         PayerMessage: string|null,
     *         ProcessorName: string|null,
     *         ProcessorResult: string|null,
     *         ProcessorMessage: string|null
     *     }
     * }
     */
    public function getResponseData(): array
    {
        return $this->responseData;
    }
}
