FROM php:8.2-fpm-alpine

# 1. Installation des dépendances système (PHP, DB drivers, Nginx, Composer)
RUN apk add --no-cache \
    nginx \
    libpng-dev \
    libxml2-dev \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    zip \
    unzip \
    git \
    curl

# 2. Installation des extensions PHP indispensables (MySQL + Postgres + Zip)
RUN docker-php-ext-install pdo_mysql pdo_pgsql bcmath gd intl zip

# 3. Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Configuration du projet
WORKDIR /var/www
COPY . .

# 5. Installation des dépendances Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 6. Configuration Nginx
COPY ./docker/nginx.conf /etc/nginx/http.d/default.conf

# 7. Droits d'accès
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

# 8. Lancement de l'API (Migration, Cache et Services)
CMD php artisan storage:link && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan migrate --force && \
    php-fpm -D && \
    nginx -g "daemon off;"
