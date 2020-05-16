# Image
FROM php:7.2-cli

# Dependencies
RUN apt-get update

# Curl
RUN apt-get install -y libcurl3-dev curl && docker-php-ext-install curl

# Zip
RUN apt-get install -y libzip-dev zip && docker-php-ext-configure zip --with-libzip && docker-php-ext-install zip

# BC Math
RUN docker-php-ext-install bcmath

# Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Composer installation
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer
RUN chmod 0755 /usr/bin/composer

# Set up default directory
WORKDIR /app
