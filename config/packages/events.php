<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->extension('sylius_ui', [
        'events' => [
            'sylius.admin.order.show.payment_content' => [
                'blocks' => [
                    'refund_transition' => [
                        'template' => '@CommerceWeaversSyliusSaferpayPlugin/Admin/Order/Show/Payment/_refundTransition.html.twig',
                    ],
                ],
            ],
            'commerce_weavers_saferpay.admin.payment_method.configure_payment_methods' => [
                'blocks' => [
                    'header' => [
                        'template' => '@CommerceWeaversSyliusSaferpayPlugin/Admin/PaymentMethod/ConfigurePaymentMethods/_header.html.twig',
                        'priority' => 20,
                    ],
                    'content' => [
                        'template' => '@CommerceWeaversSyliusSaferpayPlugin/Admin/PaymentMethod/ConfigurePaymentMethods/_content.html.twig',
                        'priority' => 10,
                    ],
                ],
            ],
            'commerce_weavers_saferpay.admin.payment_method.configure_payment_methods.header' => [
                'blocks' => [
                    'title' => [
                        'template' => '@CommerceWeaversSyliusSaferpayPlugin/Admin/PaymentMethod/ConfigurePaymentMethods/Header/_headerTitle.html.twig',
                        'priority' => 20,
                    ],
                    'breadcrumb' => [
                        'template' => '@CommerceWeaversSyliusSaferpayPlugin/Admin/PaymentMethod/ConfigurePaymentMethods/Header/_breadcrumb.html.twig',
                        'priority' => 10,
                    ],
                ],
            ],
            'commerce_weavers_saferpay.admin.payment_method.configure_payment_methods.form' => [
                'blocks' => [
                    'content' => [
                        'template' => '@CommerceWeaversSyliusSaferpayPlugin/Admin/PaymentMethod/ConfigurePaymentMethods/Form/_content.html.twig',
                        'priority' => 10,
                    ],
                ],
            ],
            'commerce_weavers_saferpay.admin.payment_method.configure_payment_methods.form.content' => [
                'blocks' => [
                    'allowed_payment_methods' => [
                        'template' => '@CommerceWeaversSyliusSaferpayPlugin/Admin/PaymentMethod/ConfigurePaymentMethods/Form/_allowedPaymentMethods.html.twig',
                        'priority' => 20,
                    ],
                    'buttons' => [
                        'template' => '@CommerceWeaversSyliusSaferpayPlugin/Admin/PaymentMethod/ConfigurePaymentMethods/Form/_buttons.html.twig',
                        'priority' => 10,
                    ],
                ],
            ],
        ],
    ]);
};
