<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Processor;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\RefundFactoryInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use Payum\Core\Payum;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class RefundProcessor implements RefundProcessorInterface
{
    public function __construct(
        private TokenProviderInterface $tokenProvider,
        private Payum $payum,
        private GetStatusFactoryInterface $getStatusFactory,
        private RefundFactoryInterface $refundFactory,
    ) {
    }

    public function process(PaymentInterface $payment): void
    {
        $order = $payment->getOrder();
        Assert::notNull($order);

        $token = $this->tokenProvider->provide($payment, 'sylius_admin_order_show', ['id' => $order->getId()]);
        $gateway = $this->payum->getGateway($token->getGatewayName());

        $refund = $this->refundFactory->createNewWithModel($token);
        $gateway->execute($refund);

        $status = $this->getStatusFactory->createNewWithModel($refund->getFirstModel());
        $gateway->execute($status);

        $this->payum->getHttpRequestVerifier()->invalidate($token);
    }
}
