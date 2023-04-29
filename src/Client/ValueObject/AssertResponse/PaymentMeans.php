<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\AssertResponse;

class PaymentMeans
{
    private function __construct(
        private Brand $brand,
        private string $displayText,
        private Card $card,
    ) {
    }

    public function getBrand(): Brand
    {
        return $this->brand;
    }

    public function getDisplayText(): string
    {
        return $this->displayText;
    }

    public function getCard(): Card
    {
        return $this->card;
    }

    public function toArray(): array
    {
        return [
            'Brand' => $this->brand->toArray(),
            'DisplayText' => $this->displayText,
            'Card' => $this->card->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            Brand::fromArray($data['Brand']),
            $data['DisplayText'],
            Card::fromArray($data['Card']),
        );
    }
}
