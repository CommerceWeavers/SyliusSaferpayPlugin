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

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'password' => '#sylius_payment_method_gatewayConfig_config_password',
            'username' => '#sylius_payment_method_gatewayConfig_config_username',
        ]);
    }
}
