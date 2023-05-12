<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payment\Event;

class PaymentRefundSucceeded
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

    public function getRequestBody(): array
    {
        /** @phpstan-ignore-next-line */
        return $this->requestBody;
    }

    public function getResponseData(): array
    {
        /** @phpstan-ignore-next-line */
        return $this->responseData;
    }
}
