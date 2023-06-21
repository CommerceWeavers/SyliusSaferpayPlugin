<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider;

use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Webmozart\Assert\Assert;

final class TokenProvider implements TokenProviderInterface
{
    private const SYLIUS_SHOP_HOMEPAGE_ROUTE = 'sylius_shop_homepage';

    public function __construct(private Payum $payum)
    {
    }

    public function provideForAssert(PaymentInterface $payment, RequestConfiguration $requestConfiguration): TokenInterface
    {
        $redirectOptions = $this->getRedirectOptions($requestConfiguration);

        return $this->payum->getTokenFactory()->createToken(
            $this->getGatewayName($payment),
            $payment,
            $redirectOptions['route'],
            $redirectOptions['parameters'] ?? [],
        );
    }

    public function provideForCommandHandler(PaymentInterface $payment): TokenInterface
    {
        return $this->payum->getTokenFactory()->createToken(
            $this->getGatewayName($payment),
            $payment,
            self::SYLIUS_SHOP_HOMEPAGE_ROUTE, // not used, but it has to be a valid route
        );
    }

    public function provideForCapture(PaymentInterface $payment, RequestConfiguration $requestConfiguration): TokenInterface
    {
        $redirectOptions = $this->getRedirectOptions($requestConfiguration);

        return $this->payum->getTokenFactory()->createCaptureToken(
            $this->getGatewayName($payment),
            $payment,
            $redirectOptions['route'],
            $redirectOptions['parameters'] ?? [],
        );
    }

    public function provide(PaymentInterface $payment, string $path, array $parameters = []): TokenInterface
    {
        return $this->payum->getTokenFactory()->createToken($this->getGatewayName($payment), $payment, $path, $parameters);
    }

    public function provideForWebhook(PaymentInterface $payment, string $webhookRoute): TokenInterface
    {
        return $this->payum->getTokenFactory()->createToken(
            $this->getGatewayName($payment),
            $payment,
            $webhookRoute,
            ['order_token' => $payment->getOrder()?->getTokenValue()],
        );
    }

    private function getGatewayName(PaymentInterface $payment): string
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);

        return $gatewayConfig->getGatewayName();
    }

    /** @return  array{route: string, parameters: array|null} */
    private function getRedirectOptions(RequestConfiguration $requestConfiguration): array
    {
        /** @var array{route: string, parameters: array|null}|string $redirectOptions */
        $redirectOptions = $requestConfiguration->getParameters()->get('redirect');

        if (is_string($redirectOptions)) {
            $redirectOptions = ['route' => $redirectOptions, 'parameters' => null];
        }

        return $redirectOptions;
    }
}
