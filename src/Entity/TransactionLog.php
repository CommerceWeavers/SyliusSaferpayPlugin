<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Entity;

class TransactionLog implements TransactionLogInterface
{
    private ?int $id = null;

    private ?string $state = null;

    private ?string $description = null;

    private array $context = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }
}
