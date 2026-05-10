FROM php:8.2-fpm-alpine

# Install system deps + PHP extensions
RUN apk add --no-cache \
        bash \
        git \
        curl \
        libpng-dev \
        oniguruma-dev \
        libxml2-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy dependency files first for layer caching
COPY composer.json composer.lock* ./

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy the rest of the application
COPY . .

# Fix permissions
RUN chown -R www-data:www-data /var/www

EXPOSE 9000
CMD ["php-fpm"]
