# Specify the image
FROM php:7.3-cli

# Update dependencies
RUN apt-get update \
# Install Zip
    && apt-get install -y libzip-dev zip \
    && docker-php-ext-configure zip --with-libzip \
    && docker-php-ext-install zip \
 # Install Git
    && apt-get install -y git \
# Install Curl
    && apt-get install -y libcurl3-dev curl \
    && docker-php-ext-install curl \
# Install PCOV
    && pecl install pcov \
    && docker-php-ext-enable pcov

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Set up default directory
WORKDIR /app
