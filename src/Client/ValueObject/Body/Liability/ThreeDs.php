<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\Liability;

class ThreeDs
{
    private function __construct(
        private bool $authenticated,
        private bool $liabilityShift,
        private string $xId,
    ) {
    }

    public function getAuthenticated(): bool
    {
        return $this->authenticated;
    }

    public function getLiabilityShift(): bool
    {
        return $this->liabilityShift;
    }

    public function getXid(): string
    {
        return $this->xId;
    }

    public function toArray(): array
    {
        return [
            'Authenticated' => $this->getAuthenticated(),
            'LiabilityShift' => $this->getLiabilityShift(),
            'Xid' => $this->getXid(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['Authenticated'],
            $data['LiabilityShift'],
            $data['Xid'],
        );
    }
}
