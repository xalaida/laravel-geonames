# Image
FROM php:7.2-cli

# Update dependencies
RUN apt-get update

# Set up curl
RUN apt-get install -y libcurl3-dev curl && docker-php-ext-install curl

# Set up zip
RUN apt-get install -y libzip-dev zip && docker-php-ext-configure zip --with-libzip && docker-php-ext-install zip

# Set up BC Math extension
RUN docker-php-ext-install bcmath

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer
RUN chmod 0755 /usr/bin/composer

# Install PCOV
RUN pecl install pcov && docker-php-ext-enable pcov

# Set up default directory
WORKDIR /app
