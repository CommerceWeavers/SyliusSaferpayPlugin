<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\PaymentMethod;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface ConfigurePaymentMethodsPageInterface extends SymfonyPageInterface
{
    public function disablePaymentMethod(string $paymentMethodName): void;

    public function isPaymentMethodEnabled(string $paymentMethodName): bool;

    public function isAllPaymentMethodsEnabled(): bool;

    public function saveChanges(): void;
}
