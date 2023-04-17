<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Resolver;

use Payum\Core\Model\GatewayConfigInterface;
use Symfony\Component\Form\AbstractType;

final class SaferpayApiBaseUrlResolver extends AbstractType implements SaferpayApiBaseUrlResolverInterface
{
    public function __construct(private string $apiBaseUrl, private string $testApiBaseUrl)
    {
    }

    public function resolve(GatewayConfigInterface $gatewayConfig): string
    {
        $config = $gatewayConfig->getConfig();

        if (isset($config['sandbox']) && true === $config['sandbox']) {
            return $this->testApiBaseUrl;
        }

        return $this->apiBaseUrl;
    }
}
