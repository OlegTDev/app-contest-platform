FROM php:8.5-fpm-alpine AS base

RUN apk add --no-cache \
        libpng \
        libzip \
        libpq \
        libldap \
        libjpeg-turbo \
        libwebp \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        unzip \
        libpng-dev \
        libzip-dev \
        libpq-dev \
        openldap-dev \
        zlib-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql pgsql ldap gd zip \
    && apk del .build-deps

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN addgroup -g ${GROUP_ID} app && \
    adduser -u ${USER_ID} -G app -s /bin/sh -D app

WORKDIR /app
RUN chown app:app /app

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

EXPOSE 9000


# DEV
FROM base AS dev

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

USER app


# PROD
FROM base AS prod

RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

RUN { \
    echo zend_extension=opcache.so; \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini


USER app
