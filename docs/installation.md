## Installation

### 1. Require plugin with composer

```bash
composer require commerceweavers/sylius-saferpay-plugin
```

### 2. Make sure the plugin is added to list of plugins

```php
// config/bundles.php

CommerceWeavers\SyliusSaferpayPlugin\CommerceWeaversSyliusSaferpayPlugin::class => ['all' => true],
```

### 3. Import configuration

```yaml
# config/packages/_sylius.yaml

imports:
    ...
    - { resource: "@CommerceWeaversSyliusSaferpayPlugin/config/config.yaml" }
```

### 4. Import routes

```yaml
# config/routes/sylius_shop.yaml

commerce_weavers_sylius_saferpay_shop:
    resource: "@CommerceWeaversSyliusSaferpayPlugin/config/shop_routing.yml"
    prefix: /{_locale}
    requirements:
        _locale: ^[a-z]{2}(?:_[A-Z]{2})?$

# config/routes/sylius_admin.yaml

commerce_weavers_sylius_saferpay_admin:
    resource: "@CommerceWeaversSyliusSaferpayPlugin/config/admin_routing.yml"
    prefix: '/%sylius_admin.path_name%'
```

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

Prev: [Requirements](requirements.md)
Next: [Development](development.md)
