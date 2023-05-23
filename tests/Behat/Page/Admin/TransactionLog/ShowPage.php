<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\TransactionLog;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

class ShowPage extends SymfonyPage implements ShowPageInterface
{
    public function getDescription(): string
    {
        return $this->getElement('description')->getText();
    }

    public function getLogType(): string
    {
        return $this->getElement('log-type')->getText();
    }

    public function getContext(): string
    {
        return $this->getElement('context')->getText();
    }

    public function getRouteName(): string
    {
        return 'commerce_weavers_saferpay_admin_transaction_log_show';
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'context' => '[data-test-context]',
            'description' => '[data-test-description]',
            'log-type' => '[data-test-log-type]',
        ]);
    }
}
