<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StatusMarkerInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class StatusAction implements ActionInterface
{
    public const STATUS_NEW = 'NEW';

    public const STATUS_AUTHORIZED = 'AUTHORIZED';

    public const STATUS_CAPTURED = 'CAPTURED';

    public const STATUS_CANCELLED = 'CANCELLED';

    public const STATUS_REFUNDED = 'REFUNDED';

    public const STATUS_FAILED = 'FAILED';

    public function __construct(
        private StatusMarkerInterface $statusMarker,
    ) {
    }

    /** @param GetStatus $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        if ($this->statusMarker->canBeMarkedAsNew($request)) {
            $this->statusMarker->markAsNew($request);

            return;
        }

        if ($this->statusMarker->canBeMarkedAsAuthorized($request)) {
            $this->statusMarker->markAsAuthorized($request);

            return;
        }

        if ($this->statusMarker->canBeMarkedAsCaptured($request)) {
            $this->statusMarker->markAsCaptured($request);

            return;
        }

        if ($this->statusMarker->canBeMarkedAsCancelled($request)) {
            $this->statusMarker->markAsCancelled($request);

            return;
        }

        $this->statusMarker->markAsFailed($request);
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatus && $request->getFirstModel() instanceof SyliusPaymentInterface;
    }
}
