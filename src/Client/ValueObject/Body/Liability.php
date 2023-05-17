<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Liability\ThreeDs;

class Liability
{
    private function __construct(
        private bool $liabilityShift,
        private string $liableEntity,
        private ThreeDs $threeDs,
    ) {
    }

    public function getLiabilityShift(): bool
    {
        return $this->liabilityShift;
    }

    public function getLiableEntity(): string
    {
        return $this->liableEntity;
    }

    public function getThreeDs(): ThreeDs
    {
        return $this->threeDs;
    }

    public function toArray(): array
    {
        return [
            'LiabilityShift' => $this->getLiabilityShift(),
            'LiableEntity' => $this->getLiableEntity(),
            'ThreeDs' => $this->getThreeDs()->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['LiabilityShift'],
            $data['LiableEntity'],
            ThreeDs::fromArray($data['ThreeDs']),
        );
    }
}
