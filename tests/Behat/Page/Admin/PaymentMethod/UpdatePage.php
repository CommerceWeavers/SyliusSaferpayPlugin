<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\Crud\UpdatePage as BaseUpdatePage;

final class UpdatePage extends BaseUpdatePage implements UpdatePageInterface
{
    public function enableDebugMode(): void
    {
        $debugMode = $this->getElement('debug_mode');

        if (!$debugMode->isChecked()) {
            $debugMode->check();
        }
    }

    public function disableDebugMode(): void
    {
        $debugMode = $this->getElement('debug_mode');

        if ($debugMode->isChecked()) {
            $debugMode->uncheck();
        }
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'debug_mode' => '#sylius_payment_method_gatewayConfig_config_debug',
        ]);
    }
}
