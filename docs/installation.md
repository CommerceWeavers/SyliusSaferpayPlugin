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
        _locale: ^[a-z]{2}(?:_[A-Z]{2})?$

commerce_weavers_sylius_saferpay_admin:
    resource: "@CommerceWeaversSyliusSaferpayPlugin/config/admin_routing.yml"
    prefix: '/%sylius_admin.path_name%'
```

### 5. Execute migrations

```
bin/console doctrine:migrations:migrate -n
```

### 6. Copy templates that are overridden by the plugin

```
cp -fr vendor/commerce-weavers/sylius-saferpay-plugin/templates/bundles/* templates/bundles
```

#### BEWARE!

If you're using a `@SyliusShopBundle/Checkout/SelectPayment/_choice.html.twig` template provided by default with Sylius-Standard
for PayPal integration, you need to integrate its changes with the one provided by this plugin.

### 7. Add admin entry in the Webpack configuration 

```javascript
Encore
  .setOutputPath('public/build/admin/')
  .setPublicPath('/build/admin')
  .addEntry('admin-entry', './vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/Resources/private/entry.js')
  .addEntry('cw-saferpay-admin-entry', './vendor/commerce-weavers/sylius-saferpay-plugin/assets/admin/entry.js') // <-- Add this line
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader();
```

and run:

```
yarn build
```

### 7. Rebuild the cache

```bash
bin/console cache:clear
bin/console cache:warmup
```

---

Next: [Configuration](configuration.md)
