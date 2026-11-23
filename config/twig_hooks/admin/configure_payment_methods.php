<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $prefix = 'commerce_weavers_saferpay_admin.payment_method.configure_payment_methods';
    $templates = '@CommerceWeaversSyliusSaferpayPlugin/admin/payment_method/configure_payment_methods';

    $containerConfigurator->extension('sylius_twig_hooks', [
        'hooks' => [
            $prefix => [
                'sidebar' => ['template' => '@SyliusAdmin/shared/crud/common/sidebar.html.twig', 'priority' => 200],
                'navbar' => ['template' => '@SyliusAdmin/shared/crud/common/navbar.html.twig', 'priority' => 100],
                'content' => ['template' => '@SyliusAdmin/shared/crud/common/content.html.twig', 'priority' => 0],
            ],
            $prefix . '.content' => [
                'flashes' => ['template' => '@SyliusAdmin/shared/crud/common/content/flashes.html.twig', 'priority' => 200],
                'header' => ['template' => '@SyliusAdmin/shared/crud/common/content/header.html.twig', 'priority' => 100],
                'form' => ['template' => $templates . '/content/form.html.twig', 'priority' => 0],
            ],
            $prefix . '.content.header' => [
                'breadcrumbs' => ['template' => $templates . '/content/header/breadcrumbs.html.twig', 'priority' => 100],
                'title_block' => ['template' => '@SyliusAdmin/shared/crud/common/content/header/title_block.html.twig', 'priority' => 0],
            ],
            $prefix . '.content.header.title_block' => [
                'title' => ['template' => $templates . '/content/header/title.html.twig', 'priority' => 0],
            ],
        ],
    ]);
};
