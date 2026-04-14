# --- Étape 2 : Production ---
FROM php:8.2-fpm-alpine

# 1. Installation des dépendances système (On garde libzip et icu-libs)
RUN apk add --no-cache \
    nginx \
    libpng \
    libxml2 \
    icu-libs \
    libzip \
    postgresql-libs

# 2. Installation des extensions PHP (AJOUT de pdo_pgsql)
RUN apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libxml2-dev \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql bcmath gd intl zip \
    && apk del .build-deps 

# --- Le reste demeure inchangé ---
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
