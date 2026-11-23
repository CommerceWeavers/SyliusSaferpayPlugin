## Installation

### 1. Require plugin with composer

```bash
composer require commerce-weavers/sylius-saferpay-plugin
```

### 2. Make sure the plugin is added to list of plugins

```php
// config/bundles.php

CommerceWeavers\SyliusSaferpayPlugin\CommerceWeaversSyliusSaferpayPlugin::class => ['all' => true],
```

### 3. Import configuration

```yaml
# config/packages/cw_sylius_saferpay_plugin.yaml

imports:
    - { resource: "@CommerceWeaversSyliusSaferpayPlugin/config/config.yaml" }
```

### 4. Import routes

```yaml
# config/routes/cw_sylius_saferpay_plugin.yaml

commerce_weavers_sylius_saferpay_shop:
    resource: "@CommerceWeaversSyliusSaferpayPlugin/config/shop_routing.yml"
    prefix: /{_locale}
    requirements:
        _locale: ^[A-Za-z]{2,4}(_([A-Za-z]{4}|[0-9]{3}))?(_([A-Za-z]{2}|[0-9]{3}))?$

commerce_weavers_sylius_saferpay_admin:
    resource: "@CommerceWeaversSyliusSaferpayPlugin/config/admin_routing.yml"
    prefix: '/%sylius_admin.path_name%'
```

The plugin relies on the Payum-based checkout, so make sure the Sylius Payum routes are imported as well
(they are part of Sylius-Standard 2.x):

```yaml
# config/routes/sylius_shop.yaml

sylius_shop_payum:
    resource: "@SyliusPayumBundle/Resources/config/routing/integrations/sylius_shop.yaml"
```

No template overrides and no additional assets are needed: the admin and shop UI is plugged in via Sylius Twig Hooks
and uses the Sylius 2 admin and shop builds.

### 5. Execute migrations

```
bin/console doctrine:migrations:migrate -n
```

### 6. Rebuild the cache

```bash
bin/console cache:clear
bin/console cache:warmup
```

---

Next: [Configuration](configuration.md)
