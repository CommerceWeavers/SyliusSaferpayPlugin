## Development

```bash
git clone git@github.com:CommerceWeavers/SyliusSaferpayPlugin.git
composer install
(cd tests/Application && yarn install)
(cd tests/Application && yarn build)
(cd tests/Application && bin/console assets:install)
(cd tests/Application && bin/console doctrine:database:create)
(cd tests/Application && bin/console doctrine:migrations:migrate -n)
composer link-templates
```

### Opening Sylius with your plugin

- Using `dev` environment:

    ```bash
    (cd tests/Application && bin/console sylius:fixtures:load)
    (cd tests/Application && symfony serve)
    ```

- Using `test` environment:

    ```bash
    (cd tests/Application && APP_ENV=test bin/console sylius:fixtures:load)
    (cd tests/Application && APP_ENV=test symfony serve)
    ```

---

Prev: [Configuration](configuration.md)
Next: [Testing](testing.md)
