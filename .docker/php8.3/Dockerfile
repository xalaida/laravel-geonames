# Specify the image
FROM php:8.3-cli

# Update dependencies
RUN apt-get update \
# Install Zip
    && apt-get install -y libzip-dev zip \
    && docker-php-ext-install zip \
# Install Git
    && apt-get install -y git \
# Install Curl
    && apt-get install -y libcurl3-dev curl \
    && docker-php-ext-install curl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Specify the working directory
WORKDIR /app
