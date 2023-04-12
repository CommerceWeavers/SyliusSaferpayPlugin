<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\PaymentMethod\CreatePage as BaseCreatePage;

class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    public function setSaferpayUsername(string $username): void
    {
        $this->getElement('username')->setValue($username);
    }

    public function setSaferpayPassword(string $password): void
    {
        $this->getElement('password')->setValue($password);
    }

    public function setSaferpayCustomerId(string $customerId): void
    {
        $this->getElement('customer_id')->setValue($customerId);
    }

    public function setSaferpayTerminalId(string $terminalId): void
    {
        $this->getElement('terminal_id')->setValue($terminalId);
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'customer_id' => '#sylius_payment_method_gatewayConfig_config_customer_id',
            'password' => '#sylius_payment_method_gatewayConfig_config_password',
            'terminal_id' => '#sylius_payment_method_gatewayConfig_config_terminal_id',
            'username' => '#sylius_payment_method_gatewayConfig_config_username',
        ]);
    }
}
