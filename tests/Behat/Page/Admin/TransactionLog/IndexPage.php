<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Page\Admin\TransactionLog;

use Sylius\Behat\Page\Admin\Crud\IndexPage as BaseIndexPage;

final class IndexPage extends BaseIndexPage implements IndexPageInterface
{
    public function hasEntryWithTypeAndDescription(string $logType, string $logDescription, string $orderNumber): bool
    {
        $tableAccessor = $this->getTableAccessor();
        $table = $this->getElement('table');

        $rowsMeetingCriteria = $tableAccessor->getRowsWithFields(
            $table,
            [
                'type' => $logType,
                'description' => $logDescription,
                'orderNumber' => trim($orderNumber, '#'),
            ]
        );

        return count($rowsMeetingCriteria) === 1;
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'table' => '.table',
        ]);
    }
}
