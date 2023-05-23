<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\TransactionLog\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $newSubmenu = $menu
            ->addChild('commerce_weavers_sylius_saferpay_plugin')
            ->setLabel('commerce_weavers_saferpay.ui.plugin_name')
        ;

        $newSubmenu
            ->addChild('commerce_weavers_sylius_saferpay_plugin_transaction_log', [
                'route' => 'commerce_weavers_saferpay_admin_transaction_log_index',
            ])
            ->setLabel('commerce_weavers_saferpay.ui.transaction_logs')
        ;
    }
}
