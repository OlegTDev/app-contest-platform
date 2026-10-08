FROM php:8.5-cli-alpine AS base

# 1. Сюда добавлены bash, git, openssh-client, а также nodejs и npm!
RUN apk add --no-cache \
        bash \
        git \
        openssh-client \
        nodejs \
        npm \
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

# Изменили дефолтную оболочку пользователя на /bin/bash для удобства
RUN addgroup -g ${GROUP_ID} app && \
    adduser -u ${USER_ID} -G app -s /bin/bash -D app

WORKDIR /app
RUN chown app:app /app

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer


# DEV
FROM base AS dev

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini


# PROD
FROM base AS prod

RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

USER app
