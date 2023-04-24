<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Status;

use Payum\Core\Request\GetStatusInterface;

interface StateMarkerInterface
{
    public function canBeMarkedAsNew(GetStatusInterface $status): bool;

    public function canBeMarkedAsAuthorized(GetStatusInterface $status): bool;

    public function canBeMarkedAsCaptured(GetStatusInterface $status): bool;

    public function canBeMarkedAsCancelled(GetStatusInterface $status): bool;

    public function markAsNew(GetStatusInterface $status): void;

    public function markAsAuthorized(GetStatusInterface $status): void;

    public function markAsCaptured(GetStatusInterface $status): void;

    public function markAsCancelled(GetStatusInterface $status): void;

    public function markAsFailed(GetStatusInterface $status): void;
}
