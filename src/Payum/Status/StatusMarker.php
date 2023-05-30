<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Status;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\StatusCannotBeAuthorizedException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\StatusCannotBeCancelledException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\StatusCannotBeCapturedException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\StatusCannotBeNewException;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class StatusMarker implements StatusMarkerInterface
{
    public function __construct(private StatusCheckerInterface $statusChecker)
    {
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

    public function canBeMarkedAsCancelled(GetStatusInterface $status): bool
    {
        $payment = $status->getFirstModel();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        return $this->statusChecker->isCancelled($payment);
    }

    public function markAsNew(GetStatusInterface $status): void
    {
        if (!$this->canBeMarkedAsNew($status)) {
            throw new StatusCannotBeNewException();
        }

        $status->markNew();
    }

    public function markAsAuthorized(GetStatusInterface $status): void
    {
        if (!$this->canBeMarkedAsAuthorized($status)) {
            throw new StatusCannotBeAuthorizedException();
        }

        $status->markAuthorized();
    }

    public function markAsCaptured(GetStatusInterface $status): void
    {
        if (!$this->canBeMarkedAsCaptured($status)) {
            throw new StatusCannotBeCapturedException();
        }

        $status->markCaptured();
    }

    public function markAsCancelled(GetStatusInterface $status): void
    {
        if (!$this->canBeMarkedAsCancelled($status)) {
            throw new StatusCannotBeCancelledException();
        }

        $status->markCanceled();
    }

    public function markAsFailed(GetStatusInterface $status): void
    {
        $status->markFailed();
    }
}
