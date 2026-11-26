FROM dunglas/frankenphp:1-php8.2

# Install system dependencies and PHP extensions required by Laravel & Worksuite
RUN install-php-extensions \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache \
    gmp \
    soap

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application files
COPY . .

# Install PHP dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Ensure writable directories for Laravel
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache public/user-uploads \
    && chmod -R 775 storage bootstrap/cache

# Configure FrankenPHP Caddy server for Laravel public/ directory
ENV SERVER_NAME=":8080"
ENV CADDY_GLOBAL_OPTIONS="auto_https off"

EXPOSE 8080

CMD ["sh", "-c", "frankenphp php-server --root /app/public --listen :${PORT:-8080}"]
