#!/bin/sh
set -e

# Only bootstrap when starting the web server (not for `docker compose run app <cmd>`)
if [ "$1" = "frankenphp" ]; then
    cd /app

    if [ ! -f vendor/autoload.php ]; then
        echo "Installing PHP dependencies..."
        composer install --no-interaction --prefer-dist
    fi

    cd /app/tests/Application

    if [ ! -f public/build/shop/manifest.json ] || [ ! -f public/build/admin/manifest.json ]; then
        echo "Building frontend assets..."
        yarn install --non-interactive
        yarn build
    fi

    echo "Installing bundle assets..."
    php bin/console assets:install public --no-interaction >/dev/null

    for env in dev test; do
        if [ ! -f "config/encryption/${env}.key" ]; then
            echo "Generating ${env} payment encryption key..."
            APP_ENV=$env php bin/console sylius:payment:generate-key --no-interaction
        fi

        echo "Preparing ${env} database..."
        APP_ENV=$env php bin/console doctrine:database:create --if-not-exists --no-interaction
        APP_ENV=$env php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
    done

    if [ "$(php bin/console dbal:run-sql 'SELECT COUNT(*) AS c FROM sylius_channel' 2>/dev/null | grep -Eo '[0-9]+' | tail -1)" = "0" ]; then
        echo "Loading fixtures..."
        php bin/console sylius:fixtures:load --no-interaction
    fi

    php bin/console cache:warmup >/dev/null
fi

exec "$@"
