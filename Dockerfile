FROM php:8.2-fpm-alpine

# Installazione dipendenze
RUN apk add --no-cache \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    curl-dev \
    mariadb-dev && \
    docker-php-ext-install pdo_mysql bcmath gd zip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiamo tutto (incluso il file ca.pem che hai appena aggiunto)
COPY . .

ENV COMPOSER_MEMORY_LIMIT=-1
RUN php -d memory_limit=-1 /usr/bin/composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --ignore-platform-reqs --no-progress

# Crea storage per sessioni/cache e imposta permessi
RUN mkdir -p /var/www/html/storage/app/public && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/ca.pem

RUN chmod +x docker-entrypoint.sh

EXPOSE 10000

CMD ["./docker-entrypoint.sh"]