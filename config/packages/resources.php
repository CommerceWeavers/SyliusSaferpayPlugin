<?php

declare(strict_types=1);

use CommerceWeavers\SyliusSaferpayPlugin\Controller\PaymentController;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->extension('sylius_payment', [
        'resources' => [
            'payment' => [
                'classes' => [
                    'controller' => PaymentController::class,
                ],
            ],
        ],
    ]);
};
