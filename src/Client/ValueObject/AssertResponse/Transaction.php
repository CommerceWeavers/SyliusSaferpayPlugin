<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;

class Transaction
{
    public function __construct(
        private string $type,
        private string $status,
        private string $id,
        private string $date,
        private Amount $amount,
        private string $acquirerName,
        private string $acquirerReference,
        private string $sixTransactionReference,
        private string $approvalCode,
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

    public function getAcquirerName(): string
    {
        return $this->acquirerName;
    }

    public function getAcquirerReference(): string
    {
        return $this->acquirerReference;
    }

    public function getSixTransactionReference(): string
    {
        return $this->sixTransactionReference;
    }

    public function getApprovalCode(): string
    {
        return $this->approvalCode;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['Type'],
            $data['Status'],
            $data['Id'],
            $data['Date'],
            Amount::fromArray($data['Amount']),
            $data['AcquirerName'],
            $data['AcquirerReference'],
            $data['SixTransactionReference'],
            $data['ApprovalCode'],
        );
    }
}
