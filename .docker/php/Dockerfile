FROM php:7.3-fpm

RUN apt-get update && apt-get install -y \
    locales \
    libpng-dev \
    libzip-dev \
    vim \
    git \
    curl \
    gosu \
    acl

COPY local.ini /usr/local/etc/php/conf.d/local.ini

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install zip
RUN docker-php-ext-install gd

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint

RUN chmod +x /usr/local/bin/docker-entrypoint

WORKDIR /var/www/task-manager

ENTRYPOINT ["docker-entrypoint"]

EXPOSE 9000

CMD ["php-fpm"]
