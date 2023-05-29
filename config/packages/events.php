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
        ],
    ]);
};
