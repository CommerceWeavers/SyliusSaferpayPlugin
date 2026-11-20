ARG PHP_VERSION=8.4

FROM dunglas/frankenphp:1-php${PHP_VERSION}-bookworm AS php_base

RUN install-php-extensions apcu exif gd intl opcache pdo_mysql zip xdebug

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s ../lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && npm install -g yarn@1.22.22 \
    && apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client git unzip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

FROM php_base AS php_dev

ENV APP_DEBUG=1
# Xdebug is installed but off; enable with XDEBUG_MODE=debug in .env
ENV XDEBUG_MODE=off

COPY .docker/php/dev/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"
COPY .docker/Caddyfile /etc/frankenphp/Caddyfile
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 80

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
