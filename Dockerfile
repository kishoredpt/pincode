FROM php:8.3-fpm-alpine
RUN docker-php-ext-install pdo pdo_mysql opcache
WORKDIR /var/www/html
COPY . .
RUN mkdir -p assets/cache && chown -R www-data:www-data assets/cache
CMD ["php-fpm"]
