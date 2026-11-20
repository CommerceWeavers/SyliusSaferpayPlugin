<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('sylius_twig_hooks', [
        'hooks' => [
            'sylius_shop.shared.form.select_payment.payment.choice.details' => [
                'saferpay_payment_method_logos' => [
                    'template' => '@CommerceWeaversSyliusSaferpayPlugin/shop/shared/form/select_payment/payment/choice/details/saferpay_payment_method_logos.html.twig',
                    'priority' => -100,
                ],
            ],
        ],
    ]);
};
