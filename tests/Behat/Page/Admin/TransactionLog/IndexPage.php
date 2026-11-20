<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\TransactionLog;

use Behat\Mink\Element\NodeElement;
use Sylius\Behat\Page\Admin\Crud\IndexPage as BaseIndexPage;
use Webmozart\Assert\Assert;

class IndexPage extends BaseIndexPage implements IndexPageInterface
{
    public function openLogDetailsByOrderNumberAndDescription(string $orderNumber, string $description): void
    {
        $row = $this->getRow($orderNumber, $description);
        $showAction = $row->find('css', '[data-test-show-action]');
        Assert::notNull($showAction);

        $showAction->click();
    }

    private function getRow(string $orderNumber, string $description): NodeElement
    {
        $tableAccessor = $this->getTableAccessor();
        $table = $this->getElement('table');

        return $tableAccessor->getRowWithFields($table, ['orderNumber' => $orderNumber, 'description' => $description]);
    }
}
