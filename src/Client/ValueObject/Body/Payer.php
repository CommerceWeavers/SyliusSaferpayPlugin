<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body;

class Payer
{
    private function __construct(
        private ?string $id,
        private ?string $ipAddress,
        private ?string $ipLocation,
    ) {
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getIpLocation(): ?string
    {
        return $this->ipLocation;
    }

    public function toArray(): array
    {
        return [
            'Id' => $this->getId(),
            'IpAddress' => $this->getIpAddress(),
            'IpLocation' => $this->getIpLocation(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['Id'] ?? null,
            $data['IpAddress'] ?? null,
            $data['IpLocation'] ?? null,
        );
    }
}
