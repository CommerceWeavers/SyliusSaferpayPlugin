<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Client;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientBodyFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;

use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Operator\TemporaryRequestIdFileOperatorInterface;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Operator\TemporaryTokenOperatorInterface;

final class FakeSaferpayClientBodyFactory implements SaferpayClientBodyFactoryInterface
{
    public function __construct(
        private SaferpayClientBodyFactoryInterface $decoratedClientBodyFactory,
        private TemporaryTokenOperatorInterface $temporaryTokenOperator,
        private TemporaryRequestIdFileOperatorInterface $temporaryRequestIdOperator,
    ) {
    }

    public function createForAuthorize(PaymentInterface $payment, TokenInterface $token): array
    {
        $body = $this->decoratedClientBodyFactory->createForAuthorize($payment, $token);

        if ($this->temporaryRequestIdOperator->hasRequestId()) {
            $body['RequestHeader']['RequestId'] = $this->temporaryRequestIdOperator->getRequestId();
        }

        return $body;
    }

    public function createForAssert(PaymentInterface $payment): array
    {
        $body = $this->decoratedClientBodyFactory->createForAssert($payment);

        if ($this->temporaryRequestIdOperator->hasRequestId()) {
            $body['RequestHeader']['RequestId'] = $this->temporaryRequestIdOperator->getRequestId();
        }

        if ($this->temporaryTokenOperator->hasToken()) {
            $body['Token'] = $this->temporaryTokenOperator->getToken();
        }

        return $body;
    }

    public function createForCapture(PaymentInterface $payment): array
    {
        $body = $this->decoratedClientBodyFactory->createForCapture($payment);

        if ($this->temporaryRequestIdOperator->hasRequestId()) {
            $body['RequestHeader']['RequestId'] = $this->temporaryRequestIdOperator->getRequestId();
        }

        return $body;
    }

    public function createForRefund(PaymentInterface $payment): array
    {
        $body = $this->decoratedClientBodyFactory->createForRefund($payment);

        if ($this->temporaryRequestIdOperator->hasRequestId()) {
            $body['RequestHeader']['RequestId'] = $this->temporaryRequestIdOperator->getRequestId();
        }

        return $body;
    }

    public function provideHeadersForTerminal(): array
    {
        return $this->decoratedClientBodyFactory->provideHeadersForTerminal();
    }
}
