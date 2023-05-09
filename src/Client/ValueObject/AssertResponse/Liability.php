<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;

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
            'LiabilityShift' => $this->liabilityShift,
            'LiableEntity' => $this->liableEntity,
            'ThreeDs' => $this->threeDs->toArray(),
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
