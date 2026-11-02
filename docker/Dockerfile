FROM php:8.4-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip curl libpq-dev libzip-dev libicu-dev \
    && docker-php-ext-install pdo_pgsql pgsql zip intl bcmath pcntl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php.ini /usr/local/etc/php/conf.d/lunadrive.ini
COPY docker/entrypoint.sh /usr/local/bin/lunadrive-entrypoint
RUN chmod +x /usr/local/bin/lunadrive-entrypoint

ENV TMPDIR=/var/www/html/storage/framework/tmp

WORKDIR /var/www/html

ENTRYPOINT ["lunadrive-entrypoint"]
CMD ["php-fpm"]
