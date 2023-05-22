<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Hook;

use Behat\Behat\Context\Context;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Operator\TemporaryRequestIdOperatorInterface;
use Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Operator\TemporaryTokenOperatorInterface;

final class RequestIdContext implements Context
{
    public function __construct(private TemporaryRequestIdOperatorInterface $temporaryRequestIdOperator)
    {
    }

    /** @AfterScenario */
    public function deleteTemporaryRequestIdFile(): void
    {
        $this->temporaryRequestIdOperator->clearRequestId();
    }
}
