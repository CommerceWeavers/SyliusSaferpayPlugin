<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Hook;

use Behat\Behat\Context\Context;

final class TokenContext implements Context
{
    public function __construct(private string $projectDirectory)
    {
    }

    /** @AfterScenario */
    public function deleteTemporaryTokenFile(): void
    {
        if (file_exists($this->projectDirectory . '/var/temporaryToken.txt')) {
            unlink($this->projectDirectory . '/var/temporaryToken.txt');
        }
    }
}
