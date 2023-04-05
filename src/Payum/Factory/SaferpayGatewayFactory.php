<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\ValueObject\SaferpayApi;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Webmozart\Assert\Assert;

class SaferpayGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'saferpay',
            'payum.factory_title' => 'Saferpay',
        ]);

        $config['payum.api'] = function (ArrayObject $config) {
            $username = $config['username'];
            $password = $config['password'];

            Assert::string($username);
            Assert::string($password);

            return new SaferpayApi($username, $password);
        };
    }
}
