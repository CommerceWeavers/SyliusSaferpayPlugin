<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Header\ResponseHeader;

class ErrorResponse implements ResponseInterface
{
    private function __construct(
        private int $statusCode,
        private ResponseHeader $responseHeader,
        private string $name,
        private string $message,
        private array $detail,
        private string $behavior,
        private string $failedOperation,
        private ?string $transactionId = null,
        private ?string $orderId = null,
        private ?string $payerMessage = null,
        private ?string $processorName = null,
        private ?string $processorResult = null,
        private ?string $processorMessage = null,
    ) {
    }

    public static function forAssert(array $data): self
    {
        return self::createForOperation($data, 'Assert');
    }

    public static function forCapture(array $data): self
    {
        return self::createForOperation($data, 'Capture');
    }

    public static function forAuthorize(array $data): self
    {
        return self::createForOperation($data, 'Authorize');
    }

    public static function forRefund(array $data): self
    {
        return self::createForOperation($data, 'Refund');
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseHeader(): ResponseHeader
    {
        return $this->responseHeader;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDetail(): array
    {
        return $this->detail;
    }

    public function getBehavior(): string
    {
        return $this->behavior;
    }

    public function getFailedOperation(): string
    {
        return $this->failedOperation;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function getPayerMessage(): ?string
    {
        return $this->payerMessage;
    }

    public function getProcessorName(): ?string
    {
        return $this->processorName;
    }

    public function getProcessorResult(): ?string
    {
        return $this->processorResult;
    }

    public function getProcessorMessage(): ?string
    {
        return $this->processorMessage;
    }

    public function toArray(): array
    {
        return [
            'StatusCode' => $this->getStatusCode(),
            'ResponseHeader' => $this->getResponseHeader()->toArray(),
            'Name' => $this->getName(),
            'Message' => $this->getMessage(),
            'Behavior' => $this->getBehavior(),
            'TransactionId' => $this->getTransactionId(),
            'OrderId' => $this->getOrderId(),
            'FailedOperation' => $this->getFailedOperation(),
            'PayerMessage' => $this->getPayerMessage(),
            'ProcessorName' => $this->getProcessorName(),
            'ProcessorResult' => $this->getProcessorResult(),
            'ProcessorMessage' => $this->getProcessorMessage(),
        ];
    }

    public function isSuccessful(): bool
    {
        return false;
    }

    private static function createForOperation(array $data, string $operation): self
    {
        return new self(
            $data['StatusCode'],
            ResponseHeader::fromArray($data['ResponseHeader']),
            $data['ErrorName'],
            $data['ErrorMessage'],
            $data['ErrorDetail'] ?? [],
            $data['Behavior'],
            $operation,
            $data['TransactionId'] ?? null,
            $data['OrderId'] ?? null,
            $data['PayerMessage'] ?? null,
            $data['ProcessorName'] ?? null,
            $data['ProcessorResult'] ?? null,
            $data['ProcessorMessage'] ?? null,
        );
    }
}
