<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\PaymentMethod\CreatePageInterface as BaseCreatePageInterface;

interface CreatePageInterface extends BaseCreatePageInterface
{
    public function setSaferpayUsername(string $username): void;

    public function setSaferpayPassword(string $password): void;

    public function setSaferpayCustomerId(string $customerId): void;

    public function setSaferpayTerminalId(string $terminalId): void;
}
