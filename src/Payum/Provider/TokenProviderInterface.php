<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider;

use Payum\Core\Security\TokenInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Core\Model\PaymentInterface;

interface TokenProviderInterface
{
    public function provideForAssert(PaymentInterface $payment, RequestConfiguration $requestConfiguration): TokenInterface;

    public function provideForCapture(PaymentInterface $payment, RequestConfiguration $requestConfiguration): TokenInterface;

    public function provide(PaymentInterface $payment, string $path, array $parameters = []): TokenInterface;
}
