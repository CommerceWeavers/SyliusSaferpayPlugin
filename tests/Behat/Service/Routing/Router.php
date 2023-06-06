<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Service\Routing;

use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class Router implements RouterInterface, WarmableInterface
{
    /**
     * @param RouterInterface&WarmableInterface $baseRouter
     */
    public function __construct(
        private RouterInterface $baseRouter,
        private LocaleContextInterface $localeContext,
    ) {
    }

    public function setContext(RequestContext $context): void
    {
        $this->baseRouter->setContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->baseRouter->getContext();
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->baseRouter->getRouteCollection();
    }

    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        $locale = $this->localeContext->getLocaleCode();

        $parameters['_locale'] = $locale;

        return $this->baseRouter->generate($name, $parameters, $referenceType);
    }

    public function match(string $pathinfo): array
    {
        return $this->baseRouter->match($pathinfo);
    }

    /**
     * @return array<string>
     */
    public function warmUp(string $cacheDir): array
    {
        return $this->baseRouter->warmUp($cacheDir);
    }
}
