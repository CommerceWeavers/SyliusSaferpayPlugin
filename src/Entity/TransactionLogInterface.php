<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface TransactionLogInterface extends ResourceInterface
{
    public function getState(): ?string;

    public function setState(?string $state): void;

    public function getDescription(): ?string;

    public function setDescription(?string $description): void;

    public function getContext(): array;

    public function setContext(array $context): void;
}
