<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body;

use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\PaymentMeans\Brand;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\PaymentMeans\Card;
use CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\Body\PaymentMeans\PayPal;

class PaymentMeans
{
    private function __construct(
        private Brand $brand,
        private string $displayText,
        private ?Card $card,
        private ?PayPal $payPal,
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

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function getPayPal(): ?PayPal
    {
        return $this->payPal;
    }

    public function toArray(): array
    {
        return [
            'Brand' => $this->getBrand()->toArray(),
            'DisplayText' => $this->getDisplayText(),
            'Card' => $this->getCard()?->toArray(),
            'PayPal' => $this->getPayPal()?->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            Brand::fromArray($data['Brand']),
            $data['DisplayText'],
            isset($data['Card']) ? Card::fromArray($data['Card']) : null,
            isset($data['PayPal']) ? PayPal::fromArray($data['PayPal']) : null,
        );
    }
}
