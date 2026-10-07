FROM php:8.5-cli-alpine AS base

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

# xdebug commented out due to pecl.php.net SSL issues
# RUN install-php-extensions xdebug
# RUN docker-php-ext-enable xdebug \
#     && echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#     && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#     && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#     && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN addgroup -g ${GROUP_ID} app && \
    adduser -u ${USER_ID} -G app -s /bin/sh -D app

WORKDIR /app
RUN chown app:app /app

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer


# DEV
FROM base AS dev

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

USER app


# PROD
FROM base AS prod

RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

USER app
