## Development

### Docker (recommended)

```bash
cp .env.dist .env                    # optional: change host ports (APP_PORT, MYSQL_PORT, MOCKOON_PORT)
make docker.up                       # builds the image; on first start installs dependencies, builds assets,
                                     # creates both databases, runs migrations and loads fixtures
```

- Shop: http://localhost:8080, admin: http://localhost:8080/admin (`sylius` / `sylius`)
- The fixtures contain a ready-to-use **Saferpay** payment method. Put your Saferpay test account data
  (see [Configuration](configuration.md)) into the root `.env` (passed into the container) or
  `tests/Application/.env.local`, then run `make docker.fixtures`:

    ```dotenv
    SAFERPAY_USERNAME=API_xxxxx_xxxxxxxx
    SAFERPAY_PASSWORD=...
    SAFERPAY_CUSTOMER_ID=xxxxxx
    SAFERPAY_TERMINAL_ID=xxxxxxxx
    ```

- `make docker.test` runs ECS, PHPStan, PHPUnit and Behat (Mockoon runs as a compose service)
- `make docker.shell` opens a shell in the app container; set `XDEBUG_MODE=debug` in `.env` to enable Xdebug

### Native

```bash
git clone git@github.com:CommerceWeavers/SyliusSaferpayPlugin.git
composer install
(cd tests/Application && yarn install)
(cd tests/Application && yarn build)
(cd tests/Application && bin/console assets:install)
(cd tests/Application && bin/console sylius:payment:generate-key)                 # one key per environment,
(cd tests/Application && APP_ENV=test bin/console sylius:payment:generate-key)    # see SYLIUS_PAYMENT_ENCRYPTION_KEY_PATH
(cd tests/Application && bin/console doctrine:database:create)
(cd tests/Application && bin/console doctrine:migrations:migrate -n)
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

### Sharing localhost for Saferpay access

You need to be sure that Saferpay can access your localhost to ensure proper webhooks handling. To achieve that, you can
use a tool like [ngrok](https://ngrok.com/).

---

Prev: [Configuration](configuration.md)
Next: [Testing](testing.md)
