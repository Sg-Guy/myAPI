# --- Étape 1 : On nomme cette étape "builder" ---
FROM php:8.2-fpm-alpine AS builder

RUN apk add --no-cache libpng-dev libxml2-dev zip unzip git curl icu-dev libzip-dev
RUN docker-php-ext-install pdo_mysql bcmath gd intl zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

# --- Étape 2 : Production ---
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    nginx \
    libpng \
    libpng-dev \
    libxml2-dev \
    icu-libs \
    icu-dev \
    libzip-dev \
    zlib-dev \
    oniguruma-dev

RUN docker-php-ext-install pdo_mysql bcmath gd intl zip
RUN apk del libpng-dev libxml2-dev icu-dev libzip-dev zlib-dev

# CORRECT : On utilise le nom "builder" défini à l'étape 1
COPY --from=builder /var/www /var/www
COPY ./docker/nginx.conf /etc/nginx/http.d/default.conf

WORKDIR /var/www

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

CMD php artisan storage:link && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan migrate --force && \
    php-fpm -D && \
    nginx -g "daemon off;"
