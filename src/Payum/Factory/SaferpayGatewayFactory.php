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

        $config['payum.api'] = function (ArrayObject $config): SaferpayApi {
            $username = $config['username'];
            $password = $config['password'];
            $customerId = $config['customer_id'];
            $terminalId = $config['terminal_id'];
            $sandbox = $config['sandbox'];

            Assert::string($username);
            Assert::string($password);
            Assert::string($customerId);
            Assert::string($terminalId);
            Assert::boolean($sandbox);

            return new SaferpayApi($username, $password, $customerId, $terminalId, $sandbox);
        };
    }
}
