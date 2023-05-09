<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;

class Error
{
    private function __construct(
        private string $name,
        private string $message,
        private string $behavior,
        private string $transactionId,
        private string $orderId,
        private ?string $payerMessage,
        private ?string $processorName,
        private ?string $processorResult,
        private ?string $processorMessage,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getBehavior(): string
    {
        return $this->behavior;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getOrderId(): string
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
            'Name' => $this->getName(),
            'Message' => $this->getMessage(),
            'Behavior' => $this->getBehavior(),
            'TransactionId' => $this->getTransactionId(),
            'OrderId' => $this->getOrderId(),
            'PayerMessage' => $this->getPayerMessage(),
            'ProcessorName' => $this->getProcessorName(),
            'ProcessorResult' => $this->getProcessorResult(),
            'ProcessorMessage' => $this->getProcessorMessage(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['ErrorName'],
            $data['ErrorMessage'],
            $data['Behavior'],
            $data['TransactionId'],
            $data['OrderId'],
            $data['PayerMessage'] ?? null,
            $data['ProcessorName'] ?? null,
            $data['ProcessorResult'] ?? null,
            $data['ProcessorMessage'] ?? null,
        );
    }
}
