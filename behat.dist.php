<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Profile;
use Behat\Config\TesterOptions;
use Behat\MinkExtension\ServiceContainer\MinkExtension;
use FriendsOfBehat\MinkDebugExtension\ServiceContainer\MinkDebugExtension;
use FriendsOfBehat\SuiteSettingsExtension\ServiceContainer\SuiteSettingsExtension;
use FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension;
use FriendsOfBehat\VariadicExtension\ServiceContainer\VariadicExtension;

// Sylius 2.0-2.2 ship their Behat suites as YAML, Sylius 2.3+ as PHP
$syliusSuites = file_exists(__DIR__ . '/vendor/sylius/sylius/src/Sylius/Behat/Resources/config/suites.php')
    ? 'vendor/sylius/sylius/src/Sylius/Behat/Resources/config/suites.php'
    : 'vendor/sylius/sylius/src/Sylius/Behat/Resources/config/suites.yml';

return (new Config())
    ->import([
        $syliusSuites,
        'tests/Behat/Resources/config/suites.yaml',
    ])
    ->withProfile(
        (new Profile('default'))
            // withFilter() instead of GherkinOptions (Behat 3.30+): newer behat/gherkin conflicts with Sylius 2.0.7+
            ->withFilter(new TagFilter('~@todo&&~@cli'))
            // vendor deprecations (e.g. Payum on league/uri 7) must not fail scenarios
            ->withTesterOptions((new TesterOptions())->withErrorReporting(\E_ALL & ~(\E_DEPRECATED | \E_USER_DEPRECATED)))
            ->withExtension(new Extension(MinkDebugExtension::class, [
                'directory' => 'etc/build',
                'clean_start' => false,
                'screenshot' => true,
            ]))
            ->withExtension(new Extension(MinkExtension::class, [
                'files_path' => '%paths.base%/vendor/sylius/sylius/src/Sylius/Behat/Resources/fixtures/',
                'base_url' => 'http://127.0.0.1:8080/',
                'default_session' => 'symfony',
                'sessions' => [
                    'symfony' => [
                        'symfony' => null,
                    ],
                ],
                'show_auto' => false,
            ]))
            ->withExtension(new Extension(SymfonyExtension::class, [
                'bootstrap' => 'tests/Application/config/bootstrap.php',
                'kernel' => [
                    'class' => 'Tests\CommerceWeavers\SyliusSaferpayPlugin\Application\Kernel',
                ],
            ]))
            ->withExtension(new Extension(VariadicExtension::class))
            ->withExtension(new Extension(SuiteSettingsExtension::class, [
                'paths' => [
                    'features',
                    'vendor/sylius/sylius/features/admin/order/managing_orders/handling_order_payment/placing_order_with_payment.feature',
                    'vendor/sylius/sylius/features/admin/payment/managing_payment_methods/editing_payment_method.feature',
                ],
            ])),
    )
;
