<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Client\ValueObject\CaptureResponse;

final class Error
{
    public function __construct (
        private string $behavior,
        private string $name,
        private string $message,
    ) {
    }

    public function getBehavior(): string
    {
        return $this->behavior;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function toArray(): array
    {
        return [
            'Behavior' => $this->behavior,
            'Name' => $this->name,
            'Message' => $this->message,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['Behavior'],
            $data['Name'],
            $data['Message'],
        );
    }
}
