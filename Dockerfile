# -----------------------------
# 1️⃣ Builder Stage
# -----------------------------
FROM php:8.2-fpm AS builder

WORKDIR /var/www

# System deps
RUN apt-get update && apt-get install -y \
    git curl zip unzip nodejs npm \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip gd

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy only composer files first (better cache)
COPY composer.json composer.lock ./

# 🔑 IMPORTANT: no scripts, no env needed
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# Copy rest of app
COPY . .

# Build React / Vite
RUN npm install
RUN npm run build


# -----------------------------
# 2️⃣ Runtime Stage
# -----------------------------
FROM php:8.2-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    libpng-dev libzip-dev \
    && docker-php-ext-install pdo_mysql zip gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=builder /var/www /var/www

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
