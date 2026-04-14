# Étape 1 : Build (Installation des dépendances)
FROM php:8.2-fpm-alpine as build

RUN apk add --no-cache libpng-dev libxml2-dev zip unzip git curl icu-dev libzip-dev
RUN docker-php-ext-install pdo_mysql bcmath gd intl zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

# Étape 2 : Production (Image légère avec Nginx)
FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx libpng libxml2 icu libzip pdo_mysql

# Configuration PHP-FPM et Nginx
COPY --from=build /var/www /var/www
COPY ./docker/nginx.conf /etc/nginx/http.d/default.conf

WORKDIR /var/www

# Droits d'accès pour Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/cache

EXPOSE 80

# Script de démarrage
CMD php artisan migrate --force && php artisan config:cache && php artisan storage:link && php artisan route:cache && php artisan view:cache && nginx && php-fpm
