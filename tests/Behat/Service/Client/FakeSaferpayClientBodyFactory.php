<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientBodyFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Operator\TemporaryRequestIdOperatorInterface;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Operator\TemporaryTokenOperatorInterface;

final class FakeSaferpayClientBodyFactory implements SaferpayClientBodyFactoryInterface
{
    public function __construct(
        private SaferpayClientBodyFactoryInterface $decoratedClientBodyFactory,
        private TemporaryTokenOperatorInterface $temporaryTokenOperator,
        private TemporaryRequestIdOperatorInterface $temporaryRequestIdOperator,
    ) {
    }

    public function createForAuthorize(PaymentInterface $payment, TokenInterface $token): array
    {
        return $this->decoratedClientBodyFactory->createForAuthorize($payment, $token);
    }

    public function createForAssert(PaymentInterface $payment): array
    {
        $body = $this->decoratedClientBodyFactory->createForAssert($payment);

        if ($this->temporaryTokenOperator->hasToken()) {
            $body['Token'] = $this->temporaryTokenOperator->getToken();
        }

        return $body;
    }

    public function createForCapture(PaymentInterface $payment): array
    {
        return $this->decoratedClientBodyFactory->createForCapture($payment);
    }

    public function createForRefund(PaymentInterface $payment): array
    {
        $body = $this->decoratedClientBodyFactory->createForRefund($payment);

        if ($this->temporaryRequestIdOperator->hasRequestId()) {
            $body['RequestHeader']['RequestId'] = $this->temporaryRequestIdOperator->getRequestId();
        }

        return $body;
    }
}
