ARG PHP_VERSION=5.6

FROM php:${PHP_VERSION}-apache
LABEL maintainer="Julien Bornstein <julien@parisbouge.com>"

RUN apt-get update \
    && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libxml2-dev \
        zlib1g-dev \
        libncurses5-dev \
        libicu-dev \
        libcurl4-openssl-dev \
        libssl-dev \
        libmagickwand-dev --no-install-recommends \
        zip \
        unzip \
        libzip-dev \
        vim \
        less \
        openssh-client \
        git \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install intl \
    && docker-php-ext-install soap \
    && docker-php-ext-install exif \

    && docker-php-ext-configure pdo_mysql \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install opcache \

    && docker-php-ext-configure zip --with-libzip \
    && docker-php-ext-install zip \
    && pecl install imagick \
    && pecl install mongo-1.2.12 \

    && docker-php-ext-enable \
    opcache \
    imagick \
    mongo

RUN a2enmod rewrite remoteip headers

COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/apache.conf /etc/apache2/conf-available/z-app.conf

RUN a2enconf z-app

WORKDIR /app
