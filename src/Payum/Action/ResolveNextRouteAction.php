<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRoute;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRouteInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class ResolveNextRouteAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public const PREPARE_ASSERT_ROUTE = 'commerce_weavers_sylius_saferpay_prepare_assert';

    public const PREPARE_CAPTURE_ROUTE = 'commerce_weavers_sylius_saferpay_prepare_capture';

    public const SHOW_ORDER_ROUTE = 'sylius_shop_order_show';

    /**
     * @param ResolveNextRouteInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        $paymentDetails = $payment->getDetails();

        if (StatusAction::STATUS_NEW === $paymentDetails['status']) {
            $request->setRouteName(self::PREPARE_ASSERT_ROUTE);
            $request->setRouteParameters([
                'tokenValue' => $payment->getOrder()->getTokenValue(),
            ]);

            return;
        }

        if (StatusAction::STATUS_AUTHORIZED === $paymentDetails['status']) {
            $request->setRouteName(self::PREPARE_CAPTURE_ROUTE);
            $request->setRouteParameters([
                'tokenValue' => $payment->getOrder()->getTokenValue(),
            ]);

            return;
        }

        if (StatusAction::STATUS_CAPTURED && PaymentInterface::STATE_COMPLETED === $payment->getState()) {
            $request->setRouteName(
                'sylius_shop_order_thank_you',
            );

            return;
        }

        $request->setRouteName(self::SHOW_ORDER_ROUTE);
        $request->setRouteParameters([
            'tokenValue' => $payment->getOrder()->getTokenValue(),
        ]);
    }

    public function supports($request): bool
    {
        return $request instanceof ResolveNextRoute && $request->getModel() instanceof PaymentInterface;
    }
}
