# -----------------------------
# 1️⃣ Build Stage (Composer + Node)
# -----------------------------
FROM php:8.2-fpm AS builder

WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip nodejs npm libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy app files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install and build React (Vite)
RUN npm install
RUN npm run build


# -----------------------------
# 2️⃣ Production Image (PHP-FPM)
# -----------------------------
FROM php:8.2-fpm AS app

WORKDIR /var/www

# Copy built application from builder
COPY --from=builder /var/www /var/www

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# -----------------------------
# 3️⃣ Nginx Container
# -----------------------------
FROM nginx:1.25-alpine AS nginx

COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www /var/www

EXPOSE 80
