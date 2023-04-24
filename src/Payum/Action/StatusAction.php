<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Status\StateMarkerInterface;
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

    public const STATUS_FAILED = 'FAILED';

    public function __construct(
        private StateMarkerInterface $stateMarker,
    ) {
    }

    /** @param GetStatus $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        if ($this->stateMarker->canBeMarkedAsNew($request)) {
            $this->stateMarker->markAsNew($request);

            return;
        }

        if ($this->stateMarker->canBeMarkedAsAuthorized($request)) {
            $this->stateMarker->markAsAuthorized($request);

            return;
        }

        if ($this->stateMarker->canBeMarkedAsCaptured($request)) {
            $this->stateMarker->markAsCaptured($request);

            return;
        }

        if ($this->stateMarker->canBeMarkedAsCancelled($request)) {
            $this->stateMarker->markAsCancelled($request);

            return;
        }

        $this->stateMarker->markAsFailed($request);
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatus && $request->getFirstModel() instanceof SyliusPaymentInterface;
    }
}
