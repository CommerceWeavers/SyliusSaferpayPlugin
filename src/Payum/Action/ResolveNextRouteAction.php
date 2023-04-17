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
use Webmozart\Assert\Assert;

final class ResolveNextRouteAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public const PREPARE_ASSERT_ROUTE = 'commerce_weavers_sylius_saferpay_prepare_assert';

    public const PREPARE_CAPTURE_ROUTE = 'commerce_weavers_sylius_saferpay_prepare_capture';

    public const SHOW_ORDER_ROUTE = 'sylius_shop_order_show';

    public const THANK_YOU_PAGE_ROUTE = 'sylius_shop_order_thank_you';

    /** @param ResolveNextRoute $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $paymentStatus = $this->getPaymentStatus($request);
        $paymentState = $this->getPaymentState($request);
        $orderToken = $this->getOrderToken($request);

        if ($this->isNew($paymentStatus)) {
            $request->setRouteName(self::PREPARE_ASSERT_ROUTE);
            $request->setRouteParameters([
                'tokenValue' => $orderToken,
            ]);

            return;
        }

        if ($this->isAuthorized($paymentStatus)) {
            $request->setRouteName(self::PREPARE_CAPTURE_ROUTE);
            $request->setRouteParameters([
                'tokenValue' => $orderToken,
            ]);

            return;
        }

        if ($this->isCaptured($paymentStatus) && $this->isCompleted($paymentState)) {
            $request->setRouteName(self::THANK_YOU_PAGE_ROUTE);

            return;
        }

        $request->setRouteName(self::SHOW_ORDER_ROUTE);
        $request->setRouteParameters([
            'tokenValue' => $orderToken,
        ]);
    }

    private function getPaymentStatus(ResolveNextRouteInterface $request): string
    {
        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        $paymentDetails = $payment->getDetails();

        Assert::keyExists($paymentDetails, 'status');
        Assert::string($paymentDetails['status']);

        return $paymentDetails['status'];
    }

    private function getPaymentState(ResolveNextRouteInterface $request): string
    {
        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        $state = $payment->getState();

        Assert::notNull($state);

        return $state;
    }

    private function getOrderToken(ResolveNextRouteInterface $request): string
    {
        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        $orderToken = $payment->getOrder()?->getTokenValue();

        Assert::notNull($orderToken);

        return $orderToken;
    }

    private function isNew($status): bool
    {
        return StatusAction::STATUS_NEW === $status;
    }

    private function isAuthorized($status): bool
    {
        return StatusAction::STATUS_AUTHORIZED === $status;
    }

    private function isCaptured(mixed $paymentStatus): bool
    {
        return StatusAction::STATUS_CAPTURED === $paymentStatus;
    }

    private function isCompleted(PaymentInterface $payment): bool
    {
        return PaymentInterface::STATE_COMPLETED === $payment->getState();
    }

    public function supports($request): bool
    {
        return $request instanceof ResolveNextRoute && $request->getModel() instanceof PaymentInterface;
    }
}
