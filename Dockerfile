# -----------------------------
# 1️⃣ Build Stage (Composer + Node)
# -----------------------------
FROM php:8.2-fpm AS builder

WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip nodejs npm \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application source
COPY . .

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# Install JS dependencies & build React (Vite)
RUN npm install
RUN npm run build


# -----------------------------
# 2️⃣ Runtime Stage (PHP-FPM)
# -----------------------------
FROM php:8.2-fpm AS app

WORKDIR /var/www

# Install runtime PHP extensions only
RUN apt-get update && apt-get install -y \
    libpng-dev libzip-dev \
    && docker-php-ext-install pdo_mysql zip gd \
    && rm -rf /var/lib/apt/lists/*

# Copy built app from builder
COPY --from=builder /var/www /var/www

# Fix permissions for Laravel
RUN chown -R www-data:www-data \
    storage bootstrap/cache

# Expose PHP-FPM port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
