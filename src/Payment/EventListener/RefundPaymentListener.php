<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payment\EventListener;

use CommerceWeavers\SyliusSaferpayPlugin\Processor\RefundProcessorInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Webmozart\Assert\Assert;

/**
 * Symfony Workflow counterpart of the winzou "before refund" callback:
 * runs during the transition (before the new marking is set), so a failed
 * Saferpay refund aborts the transition.
 */
final class RefundPaymentListener
{
    public function __construct(private RefundProcessorInterface $refundProcessor)
    {
    }

    public function __invoke(TransitionEvent $event): void
    {
        $payment = $event->getSubject();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        $this->refundProcessor->process($payment);
    }
}
