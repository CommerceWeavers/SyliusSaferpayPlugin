<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Payum\Factory;

use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class TokenFactoryBuilder
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function __invoke(): TokenFactoryInterface
    {
        /** @var TokenFactoryInterface $buildResult */
        $buildResult = call_user_func_array([$this, 'build'], func_get_args());

        return $buildResult;
    }

    public function build(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry): TokenFactoryInterface
    {
        return new TokenFactory($tokenStorage, $storageRegistry, $this->urlGenerator);
    }
}
