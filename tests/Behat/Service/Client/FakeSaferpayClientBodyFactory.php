<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientBodyFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class FakeSaferpayClientBodyFactory implements SaferpayClientBodyFactoryInterface
{
    private string $temporaryFilePath;

    public function __construct(
        private SaferpayClientBodyFactoryInterface $decoratedClientBodyFactory,
        string $projectDirectory,
    ) {
        $this->temporaryFilePath = $projectDirectory . '/var/temporaryToken.txt';
    }

    public function createForAuthorize(PaymentInterface $payment, TokenInterface $token): array
    {
        return $this->decoratedClientBodyFactory->createForAuthorize($payment, $token);
    }

    public function createForAssert(PaymentInterface $payment): array
    {
        $body = $this->decoratedClientBodyFactory->createForAssert($payment);

        if (file_exists($this->temporaryFilePath)) {
            $body['Token'] = file_get_contents($this->temporaryFilePath);
        }

        return $body;
    }

    public function createForCapture(PaymentInterface $payment): array
    {
        return $this->decoratedClientBodyFactory->createForCapture($payment);
    }
}
