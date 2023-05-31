<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\PaymentMethod;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

class ConfigurePaymentMethodsPage extends SymfonyPage implements ConfigurePaymentMethodsPageInterface
{
    public function getRouteName(): string
    {
        return 'commerce_weavers_sylius_saferpay_admin_configure_payment_methods';
    }

    public function disablePaymentMethod(string $paymentMethodName): void
    {
        $this->getDocument()->uncheckField($paymentMethodName);
    }

    public function isPaymentMethodEnabled(string $paymentMethodName): bool
    {
        return $this->getDocument()->hasCheckedField($paymentMethodName);
    }

    public function isAllPaymentMethodsEnabled(): bool
    {
        $inputs = $this->getElement('allowed_payment_methods')->findAll('css', 'input[type="checkbox"]');
        foreach ($inputs as $input) {
            if (!$input->isChecked()) {
                return false;
            }
        }

        return true;
    }

    public function saveChanges(): void
    {
        $this->getElement('save_changes_button')->press();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'allowed_payment_methods' => '#commerce_weavers_sylius_saferpay_payment_methods_configuration_allowed_payment_methods',
            'save_changes_button' => '#sylius_save_changes_button',
        ]);
    }
}
