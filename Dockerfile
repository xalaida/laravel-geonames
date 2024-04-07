ARG PHP_VERSION=7.3

FROM php:${PHP_VERSION}-cli

RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libzip-dev \
        libcurl3-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install \
        zip \
        curl

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /workspace
