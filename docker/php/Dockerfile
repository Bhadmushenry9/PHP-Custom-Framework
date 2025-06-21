FROM php:8.3-fpm as php

# Install system dependencies
RUN apt-get update && apt-get install -y \
    wget \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    libxml2-dev \
    libxslt-dev \
    libonig-dev \
    libpq-dev \
    libbz2-dev \
    libgmp-dev \
    libldap2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zlib1g-dev \
    libssl-dev \
    libgpgme-dev \
    g++ \
    libc-client-dev \
    libkrb5-dev \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure intl && \
    docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
        pdo_mysql \
        curl \
        zip \
        xml \
        bz2 \
        bcmath \
        intl \
        exif \
        pcntl \
        soap \
        xsl \
        ldap \
        gd

# Install Redis extension (optional, if using Redis)
RUN pecl install redis && docker-php-ext-enable redis

# Install Node.js (v18.x) and npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get update && apt-get install -y nodejs && \
    npm install -g npm

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy app source code
COPY src/ /var/www/html

# Set permissions for writable directories
RUN mkdir -p /var/www/html/storage && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage

# ──────────────────────────────
# Xdebug layer (for local dev)
# ──────────────────────────────

FROM php as php_dev

ARG XDEBUG_VERSION=3.3.0
RUN pecl install xdebug-${XDEBUG_VERSION} && docker-php-ext-enable xdebug

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
