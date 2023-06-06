<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Request;

use Payum\Core\Request\Generic;

final class ResolveNextCommand extends Generic implements ResolveNextCommandInterface
{
    private ?object $nextCommand = null;

    public function setNextCommand(?object $nextCommand): void
    {
        $this->nextCommand = $nextCommand;
    }

    public function getNextCommand(): ?object
    {
        return $this->nextCommand;
    }
}
