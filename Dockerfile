# --- Étape 1 : Build (Installation des dépendances) ---
FROM php:8.2-fpm-alpine as build

# Installation des outils de compilation
RUN apk add --no-cache libpng-dev libxml2-dev zip unzip git curl icu-dev libzip-dev
RUN docker-php-ext-install pdo_mysql bcmath gd intl zip

# Récupération de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Installation des dépendances PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# --- Étape 2 : Production (Image finale) ---
FROM php:8.2-fpm-alpine

# Installation des bibliothèques système (CORRECT : sans pdo_mysql ici)
RUN apk add --no-cache nginx libpng libxml2 icu-libs libzip

# Installation des extensions PHP (CORRECT : via docker-php-ext-install)
RUN docker-php-ext-install pdo_mysql bcmath gd intl zip

# Récupération du projet depuis l'étape build
COPY --from=build /var/www /var/www
# Configuration Nginx
COPY ./docker/nginx.conf /etc/nginx/http.d/default.conf

WORKDIR /var/www

# Permissions pour Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

# Script de démarrage : Storage Link + Cache + DB Migration + Services
# Note: On utilise "php-fpm -D" pour le mettre en arrière-plan
CMD php artisan storage:link && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan migrate --force && \
    php-fpm -D && \
    nginx -g "daemon off;"
