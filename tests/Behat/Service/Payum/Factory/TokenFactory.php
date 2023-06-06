<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Payum\Factory;

use Payum\Core\Bridge\Symfony\Security\TokenFactory as BaseTokenFactory;

class TokenFactory extends BaseTokenFactory
{
    private const DEFAULT_PARAMETERS = ['_locale' => 'en_US'];

    protected function generateUrl($path, array $parameters = []): string
    {
        return parent::generateUrl($path, $parameters + self::DEFAULT_PARAMETERS);
    }
}
