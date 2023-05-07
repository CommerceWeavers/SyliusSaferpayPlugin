<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Contract;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

trait SaferpayHelperTrait
{
    protected HttpBrowser $browser;

    private function initializeBrowser(): void
    {
        $this->browser = new HttpBrowser(HttpClient::create());
    }
}
