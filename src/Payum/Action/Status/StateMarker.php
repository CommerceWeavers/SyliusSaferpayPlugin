<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action\Status;

use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class StateMarker implements StateMarkerInterface
{
    public function __construct (
        private StatusCheckerInterface $statusChecker,
    ) {
    }

    public function canBeMarkedAsNew(GetStatusInterface $status): bool
    {
        $payment = $status->getFirstModel();

        Assert::isInstanceOf($payment, PaymentInterface::class);

        return $this->statusChecker->isNew($payment);
    }

    public function canBeMarkedAsAuthorized(GetStatusInterface $status): bool
    {
        $payment = $status->getFirstModel();

        Assert::isInstanceOf($payment, PaymentInterface::class);

        return $this->statusChecker->isAuthorized($payment);
    }

    public function canBeMarkedAsCaptured(GetStatusInterface $status): bool
    {
        $payment = $status->getFirstModel();

        Assert::isInstanceOf($payment, PaymentInterface::class);

        return $this->statusChecker->isCaptured($payment);
    }

    public function markAsNew(GetStatusInterface $status): void
    {
        if (!$this->canBeMarkedAsNew($status)) {
            throw new \InvalidArgumentException('Status cannot be marked as new');
        }

        $status->markNew();
    }

    public function markAsAuthorized(GetStatusInterface $status): void
    {
        if (!$this->canBeMarkedAsAuthorized($status)) {
            throw new \InvalidArgumentException('Status cannot be marked as authorized');
        }

        $status->markAuthorized();
    }

    public function markAsCaptured(GetStatusInterface $status): void
    {
        if (!$this->canBeMarkedAsCaptured($status)) {
            throw new \InvalidArgumentException('Status cannot be marked as captured');
        }

        $status->markCaptured();
    }

    public function markAsFailed(GetStatusInterface $status): void
    {
        $status->markFailed();
    }
}
