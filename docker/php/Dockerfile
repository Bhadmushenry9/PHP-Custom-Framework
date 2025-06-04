FROM php:8.3-fpm as php

# Install system dependencies
RUN set -ex; \
	apt-get update; \
	apt-get install -y \
        wget \
        libmcrypt-dev \
        libzip-dev \
        libcurl3-dev \
        libldap2-dev \
   		zlib1g-dev libicu-dev g++ \
        libc-client-dev libkrb5-dev \
        unzip \
        zip \
        git \
        libgpgme-dev \
        libxml2-dev \
        libbz2-dev \
        libxslt-dev \
    ;

# Install PHP extensions
RUN docker-php-ext-configure intl ;
RUN docker-php-ext-install ldap pdo_mysql curl zip  xml bz2 bcmath intl exif pcntl soap xsl ;
RUN apt-get install -y build-essential libssl-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev;
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/; \
    docker-php-ext-install gd;

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY src/ /var/www/html

RUN mkdir -p /var/www/html/var

# Set permissions for storage and cache
RUN chown -R www-data:www-data /var/www/html/var

FROM php as php_dev

ARG XDEBUG_VERSION=3.3.0
RUN pecl install xdebug-$XDEBUG_VERSION \
    && docker-php-ext-enable xdebug

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini