<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payment\Event;

final class PaymentAssertionFailed
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
        /** @phpstan-ignore-next-line */
        return $this->responseData;
    }
}
