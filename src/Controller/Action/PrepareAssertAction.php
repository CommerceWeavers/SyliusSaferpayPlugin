<?php

/** @noinspection ALL */

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Provider\PaymentProviderInterface;
use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

final class PrepareAssertAction
{
    public function __construct(
        private RequestConfigurationFactoryInterface $requestConfigurationFactory,
        private MetadataInterface $orderMetadata,
        private PaymentProviderInterface $paymentProvider,
        private Payum $payum,
    ) {
    }

    public function __invoke(Request $request, string $tokenValue): RedirectResponse
    {
        $requestConfiguration = $this->requestConfigurationFactory->create($this->orderMetadata, $request);
        $lastPayment = $this->paymentProvider->provideForAuthorization($tokenValue);

        $assertRequestToken = $this->createAssertToken($lastPayment, $requestConfiguration);

        return new RedirectResponse($assertRequestToken->getTargetUrl());
    }

    private function createAssertToken(PaymentInterface $payment, RequestConfiguration $requestConfiguration): TokenInterface
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);
        $gatewayName = $gatewayConfig->getGatewayName();

        /** @var array{route: string, parameters: array|null}|string $redirectOptions */
        $redirectOptions = $requestConfiguration->getParameters()->get('redirect');

        if (is_string($redirectOptions)) {
            $redirectOptions = ['route' => $redirectOptions, 'parameters' => []];
        }

        return $this->payum->getTokenFactory()->createToken(
            $gatewayName,
            $payment,
            $redirectOptions['route'],
            $redirectOptions['parameters'] ?? [],
        );
    }
}
