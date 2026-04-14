# --- Étape 2 : Production (Image finale) ---
FROM php:8.2-fpm-alpine

# 1. Installation des dépendances système + dépendances de COMPILATION
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

# 2. Installation des extensions PHP
RUN docker-php-ext-install pdo_mysql bcmath gd intl zip

# 3. Nettoyage des paquets "-dev" pour alléger l'image (optionnel mais recommandé)
RUN apk del libpng-dev libxml2-dev icu-dev libzip-dev zlib-dev

# --- Le reste demeure inchangé ---
COPY --from=build /var/www /var/www
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
