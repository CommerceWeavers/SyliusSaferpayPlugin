<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusCheckerInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRoute;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRouteInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

/** @psalm-suppress PropertyNotSetInConstructor */
final class ResolveNextRouteAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public const PREPARE_ASSERT_ROUTE = 'commerce_weavers_sylius_saferpay_prepare_assert';

    public const PREPARE_CAPTURE_ROUTE = 'commerce_weavers_sylius_saferpay_prepare_capture';

    public const SHOW_ORDER_ROUTE = 'sylius_shop_order_show';

    public const THANK_YOU_PAGE_ROUTE = 'sylius_shop_order_thank_you';

    public function __construct(private StatusCheckerInterface $statusChecker)
    {
    }

    /** @param ResolveNextRoute $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        $orderToken = $this->getOrderToken($request);

        if ($this->statusChecker->isNew($payment)) {
            $request->setRouteName(self::PREPARE_ASSERT_ROUTE);
            $request->setRouteParameters([
                'tokenValue' => $orderToken,
            ]);

            return;
        }

        if ($this->statusChecker->isAuthorized($payment)) {
            $request->setRouteName(self::PREPARE_CAPTURE_ROUTE);
            $request->setRouteParameters([
                'tokenValue' => $orderToken,
            ]);

            return;
        }

        if ($this->statusChecker->isCompleted($payment)) {
            $request->setRouteName(self::THANK_YOU_PAGE_ROUTE);

            return;
        }

        $request->setRouteName(self::SHOW_ORDER_ROUTE);
        $request->setRouteParameters([
            'tokenValue' => $orderToken,
        ]);
    }

    public function supports($request): bool
    {
        return $request instanceof ResolveNextRoute && $request->getModel() instanceof PaymentInterface;
    }

    private function getOrderToken(ResolveNextRouteInterface $request): string
    {
        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        $orderToken = $payment->getOrder()?->getTokenValue();

        Assert::notNull($orderToken);

        return $orderToken;
    }
}
