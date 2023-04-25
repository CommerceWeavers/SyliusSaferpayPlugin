<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Hook;

use Behat\Behat\Context\Context;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Operator\TemporaryTokenOperatorInterface;

final class TokenContext implements Context
{
    public function __construct(private TemporaryTokenOperatorInterface $temporaryTokenOperator)
    {
    }

    /** @AfterScenario */
    public function deleteTemporaryTokenFile(): void
    {
        $this->temporaryTokenOperator->clearToken();
    }
}
