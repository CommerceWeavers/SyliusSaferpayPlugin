<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Transaction\Amount;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Transaction\IssuerReference;

class Transaction
{
    private function __construct(
        private string $type,
        private string $status,
        private string $id,
        private string $date,
        private Amount $amount,
        private string $sixTransactionReference,
        private ?string $captureId,
        private ?string $orderId,
        private ?string $acquirerName,
        private ?string $acquirerReference,
        private ?string $approvalCode,
        private ?IssuerReference $issuerReference,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function getSixTransactionReference(): string
    {
        return $this->sixTransactionReference;
    }

    public function getCaptureId(): ?string
    {
        return $this->captureId;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function getAcquirerName(): ?string
    {
        return $this->acquirerName;
    }

    public function getAcquirerReference(): ?string
    {
        return $this->acquirerReference;
    }

    public function getApprovalCode(): ?string
    {
        return $this->approvalCode;
    }

    public function getIssuerReference(): ?IssuerReference
    {
        return $this->issuerReference;
    }

    public function toArray(): array
    {
        return [
            'Type' => $this->getType(),
            'Status' => $this->getStatus(),
            'Id' => $this->getId(),
            'Date' => $this->getDate(),
            'Amount' => $this->getAmount()->toArray(),
            'SixTransactionReference' => $this->getSixTransactionReference(),
            'CaptureId' => $this->getCaptureId(),
            'OrderId' => $this->getOrderId(),
            'AcquirerName' => $this->getAcquirerName(),
            'AcquirerReference' => $this->getAcquirerReference(),
            'ApprovalCode' => $this->getApprovalCode(),
            'IssuerReference' => $this->getIssuerReference()?->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['Type'],
            $data['Status'],
            $data['Id'],
            $data['Date'],
            Amount::fromArray($data['Amount']),
            $data['SixTransactionReference'],
            $data['CaptureId'] ?? null,
            $data['OrderId'] ?? null,
            $data['AcquirerName'] ?? null,
            $data['AcquirerReference'] ?? null,
            $data['ApprovalCode'] ?? null,
            isset($data['IssuerReference']) ? IssuerReference::fromArray($data['IssuerReference']) : null,
        );
    }
}
