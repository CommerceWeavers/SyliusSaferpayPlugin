<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Ui;

use Behat\Behat\Context\Context;

/**
 * Stands in for Sylius contexts that do not exist in every supported Sylius version
 * (e.g. "sylius.behat.context.ui.save" was introduced after Sylius 2.0).
 */
final class NoopContext implements Context
{
}
