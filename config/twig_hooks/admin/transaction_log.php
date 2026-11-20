<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('sylius_twig_hooks', [
        'hooks' => [
            'commerce_weavers_saferpay_admin.transaction_log.show.content' => [
                'body' => [
                    'template' => '@CommerceWeaversSyliusSaferpayPlugin/admin/transaction_log/show/content/body.html.twig',
                    'priority' => 0,
                ],
            ],
            'commerce_weavers_saferpay_admin.transaction_log.index.content.header.title_block' => [
                'title' => [
                    'template' => '@SyliusAdmin/shared/crud/common/content/header/title_block/title.html.twig',
                    'configuration' => [
                        'title' => 'commerce_weavers_saferpay.ui.transaction_logs',
                        'icon' => 'tabler:list-details',
                    ],
                ],
            ],
        ],
    ]);
};
