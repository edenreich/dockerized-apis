FROM composer:latest AS composer-build

WORKDIR /app

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install -o --no-dev

FROM php:cli-alpine3.10 AS swoole-build

RUN apk upgrade --update \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install swoole \
    && docker-php-ext-enable swoole \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

FROM php:cli-alpine3.10

WORKDIR /app

COPY api.php api.php

COPY --from=composer-build /app/vendor vendor
COPY --from=swoole-build /usr/local/lib/php/extensions/no-debug-non-zts-20190902/swoole.so /usr/local/lib/php/extensions/no-debug-non-zts-20190902/swoole.so

RUN docker-php-ext-enable swoole
RUN apk add --no-cache libstdc++

CMD ["php", "/app/api.php"]

EXPOSE 80
