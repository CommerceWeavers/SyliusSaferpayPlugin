<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Request;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Security\TokenAggregateInterface;

interface ResolveNextCommandInterface extends ModelAwareInterface, ModelAggregateInterface, TokenAggregateInterface
{
    /**
     * @psalm-suppress MissingReturnType
     *
     * @phpstan-ignore-next-line
     */
    public function getFirstModel();

    public function setNextCommand(?object $nextCommand): void;

    public function getNextCommand(): ?object;
}
